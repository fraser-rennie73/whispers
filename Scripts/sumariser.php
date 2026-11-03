<?php

declare(strict_types=1);

final class ArticleSummariser
{
    private string $apiKey;
    private string $model = 'gemini-3.1-flash-lite';
    private int $maxRetries = 3;
    private int $maxInputChars = 50000;
    private string $logFile;

    public function __construct()
    {
        $this->apiKey = GEMINI_API_KEY
            ?? throw new RuntimeException(
                'GEMINI_API_KEY is not configured.'
            );

        /*
        $this->logFile = $_ENV['SUMMARY_LOG_FILE']
    ?? __DIR__ . '/logs/summariser.log';
    */

        $this->logFile = './logs/summariser.log';

        $logDirectory = dirname($this->logFile);

         if (!is_dir($logDirectory)) {
              if (!mkdir($logDirectory, 0750, true) && !is_dir($logDirectory)) {
                throw new RuntimeException(
                    'Unable to create log directory.'
                );
            }
        }
    }

    private function logError(string $message, array $context = []): void
    {
        $timestamp = date('Y-m-d H:i:s');

        $line = sprintf(
            "[%s] ERROR: %s",
            $timestamp,
            $message
        );

        if ($context !== []) {
            $line .= ' ' . json_encode(
                $context,
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            );
        }

        $line .= PHP_EOL;

        file_put_contents(
            $this->logFile,
            $line,
            FILE_APPEND | LOCK_EX
         );
    }

    public function summarise(string $text): string
    {
        $text = $this->cleanInput($text);

        if ($text === '') {
            return '';
        }

        // Short articles don't need an API call.
        if (mb_strlen($text) < 350) {
            return $text;
        }

        try {
            $summary = $this->requestGemini($text);

            if ($this->isValidSummary($summary)) {
                return $summary;
            }

            $this->logError(
                'Gemini returned an invalid summary.',
                [
                    'length' => mb_strlen($summary),
                ]
             );

        } catch (Throwable $e) {
            $this->logError(
                'Gemini summarisation failed',
                [
                    'error' => $e->getMessage(),
                    'exception' => get_class($e),
                ]
            );
        }

        // Gemini failed, so use the local fallback.
        return $this->fallbackSummary($text);
    }

    private function cleanInput(string $text): string
    {
        $text = strip_tags($text);
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
        $text = trim($text);

        /*
        if (mb_strlen($text) > $this->maxInputChars) {
            $text = mb_substr($text, 0, $this->maxInputChars);
        }
        */
        
        if ( strlen($text) > $this->maxInputChars ) {
            $text = substr( $text, 0, $this->maxInputChars );
        }

        return trim($text);
    }

    private function requestGemini(string $text): string
    {
        $url =
            'https://generativelanguage.googleapis.com/v1beta/models/' .
            $this->model .
            ':generateContent?key=' .
            urlencode($this->apiKey);

        $prompt = <<<PROMPT
You are an expert editor.

Create an engaging teaser summary paragraph from the supplied article.

Requirements:
- The summary must be no more than 400 characters, including spaces.
- There is no minimum character limit.
- Use as much of the available 400 characters as appropriate, but do not pad the summary unnecessarily.
- The summary can be a single sentence or multiple sentences.
- Make it interesting and natural.
- Capture the most important and interesting points.
- Encourage the reader to continue reading.
- Do not give away every detail.
- Do not invent facts.
- Do not say "this article", "the text", or "the author".
- Return only the teaser paragraph.
- Do not use headings, bullet points, quotation marks, or markdown.

Article:

$text
PROMPT;

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.5,
                'maxOutputTokens' => 200
            ]
        ];

        $json = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );

        $lastException = null;

        for ($attempt = 0; $attempt <= $this->maxRetries; $attempt++) {

            $ch = curl_init($url);

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $json,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json'
                ],
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 20,
            ]);

            $response = curl_exec($ch);

            $curlError = curl_error($ch);
            $httpCode = (int) curl_getinfo(
                $ch,
                CURLINFO_HTTP_CODE
            );

            curl_close($ch);

            if ($response === false) {
                $this->logError(
                    'Gemini API request failed',
                    [
                        'http_code' => $httpCode,
                        'attempt' => $attempt + 1,
                        'max_attempts' => $this->maxRetries + 1,
                        'message' => $message,
                    ]
                 );

                $lastException = new RuntimeException(
                    'cURL error: ' . $curlError
                );

                if ($attempt < $this->maxRetries) {
                    $this->backoff($attempt);
                    continue;
                }

                throw $lastException;
            }

            $result = json_decode($response, true);

            if (
                $httpCode >= 200 &&
                $httpCode < 300 &&
                isset(
                    $result['candidates'][0]['content']['parts'][0]['text']
                )
            ) {
                return trim(
                    $result['candidates'][0]['content']['parts'][0]['text']
                );
            }

            $message =
                $result['error']['message']
                ?? 'Unknown Gemini API error';

            $lastException = new RuntimeException(
                "Gemini HTTP {$httpCode}: {$message}"
            );

            // Retry rate limits and temporary server errors.
            $retryable =
                $httpCode === 429 ||
                in_array(
                    $httpCode,
                    [500, 502, 503, 504],
                    true
                );

            if (!$retryable || $attempt >= $this->maxRetries) {
                throw $lastException;
            }

            $this->backoff($attempt);
        }

        throw $lastException
            ?? new RuntimeException('Gemini request failed.');
    }

    private function backoff(int $attempt): void
    {
        // 0.5s, 1s, 2s, etc., with a little randomness.
        $delay = 500000 * (2 ** $attempt);
        $delay += random_int(0, 250000);

        // Maximum 5 seconds.
        $delay = min($delay, 5000000);

        usleep($delay);
    }

    private function isValidSummary(string $summary): bool
    {
        return (trim($summary) !== '' &&
            mb_strlen($summary) <= 450);
        
        /*
        if ($summary === '' || mb_strlen($summary) > 400) {
            return false;
        }

        preg_match_all(
            '/[^.!?]+[.!?]+/u',
            $summary,
            $matches
        );

        $sentenceCount = count($matches[0]);

        return $sentenceCount >= 3 && $sentenceCount <= 5;
        */
    }

    private function fallbackSummary(string $text): string
    {
        preg_match_all(
            '/[^.!?]+[.!?]+/u',
            $text,
            $matches
        );

        $sentences = array_map('trim', $matches[0]);

        if (count($sentences) <= 4) {
            return implode(' ', $sentences);
        }

        // Basic extractive fallback.
        $stopWords = [
            'the', 'and', 'a', 'an', 'of', 'to', 'in', 'is',
            'it', 'that', 'for', 'on', 'with', 'as', 'at',
            'by', 'from', 'was', 'were', 'be', 'this', 'are',
            'or', 'has', 'had', 'have', 'but', 'not', 'they',
            'their', 'its', 'he', 'she', 'his', 'her'
        ];

        $frequency = [];

        foreach (str_word_count(strtolower($text), 1) as $word) {
            if (
                strlen($word) < 3 ||
                in_array($word, $stopWords, true)
            ) {
                continue;
            }

            $frequency[$word] =
                ($frequency[$word] ?? 0) + 1;
        }

        $scores = [];

        foreach ($sentences as $index => $sentence) {
            $score = 0;

            foreach (
                str_word_count(
                    strtolower($sentence),
                    1
                ) as $word
            ) {
                $score += $frequency[$word] ?? 0;
            }

            // Slight preference for earlier sentences.
            $score += max(0, 3 - ($index * 0.5));

            $scores[$index] = $score;
        }

        arsort($scores);

        $indexes = array_slice(
            array_keys($scores),
            0,
            4
        );

        sort($indexes);

        $selected = [];

        foreach ($indexes as $index) {
            $selected[] = $sentences[$index];
        }

        return implode(' ', $selected);
    }
}

?>

