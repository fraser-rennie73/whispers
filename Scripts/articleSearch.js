let matches = [];
let currentIndex = -1;
let debounceTimer;

document.addEventListener("DOMContentLoaded", () => {
    injectStyles();
    createSearchUI();
    articleSharer();
    
    function getArticleUrl() {
        const aUrl = document.getElementById("hdn-article-url")?.value;
        const cId = document.getElementById("hdn-category-id")?.value;
        
        let root = "https://fraser-rennie73.github.io/whispers-redirect/";
        
        let page = "index.html";
        const pages = [ 
            {id: "8", page: "magic.html"},
            {id: "11", page: "cabin.html"},
            {id: "12", page: "dream-walking.html"},
            {id: "13", page: "dream-walking-la.html"},
        ];
        const catPage = pages.find(elem => elem.id === cId);
        if(catPage)
            page = catPage.page;

        return root + page + "?" + aUrl.replace(/t=\d+\&/i, "");
}
     
function shareHeading(e) {
  const clicked = e.target;
  //alert(clicked.dataset.parentId);
  const id = clicked.dataset.parentId;
  const heading = document.getElementById(id);

  const url = getArticleUrl() + "#" + id;
  const aTitle = document.getElementById("hdn-article-title")?.value;
    
    let nextElem = heading.nextElementSibling;
    let text = "";
    const texts = [];
    
    while (text.length < 100 && nextElem) {
        /*
        if (text) text += "\n";
        text += nextElem.textContent.trim();
        nextElem = nextElem.nextElementSibling;
        */
        const thisText = nextElem.textContent.trim();
        if( thisText ) {
            texts.push(thisText);
            text += thisText;
        }
        nextElem = nextElem.nextElementSibling;
    }
    text = texts.join("\n");
    text = text.substr(0, 100) + "...";
    
    const toShare = {
        title: aTitle,
        url: url,
        text: aTitle + "\n" + heading.textContent.slice(5) + "\n" + text
    };
    navigator.share(toShare);
}

    const headings = document.querySelectorAll("h2");
    headings.forEach(heading => {
        const btn = document.createElement("button");
        btn.dataset.parentId = heading.id;
        btn.textContent = "Share";
        btn.classList.add("share-heading-button");
        btn.addEventListener("click", shareHeading);

        heading.prepend(btn);
    });
    
    function articleSharer() {
        const url = document.getElementById("hdn-article-url");
        
        if(url) {
            const shareButton = document.createElement("buton");
            shareButton.id = "shareButton";
            shareButton.textContent = "Share";
           shareButton.addEventListener("click", shareArticle);
            
            const el = document.querySelector(".comments.links");
            if(el)
                //el.parentNode.insertBefore(shareButton, el);
                el.prepend( shareButton );
            else
                document.body.prepend( shareButton );
        }
    }
    
    function shareArticle() {
        const aTitle = document.getElementById("hdn-article-title")?.value;
        const aText = document.getElementById("hdn-article-text")?.value;
        const aUrl = document.getElementById("hdn-article-url")?.value;
        const cId = document.getElementById("hdn-category-id")?.value;
        
        let root = "https://fraser-rennie73.github.io/whispers-redirect/";
        
        let page = "index.html";
        const pages = [ 
            {id: "8", page: "magic.html"},
            {id: "11", page: "cabin.html"},
            {id: "12", page: "dream-walking.html"},
            {id: "13", page: "dream-walking-la.html"},
        ];
        const catPage = pages.find((elem) => elem.id === cId);
        if(catPage)
            page = catPage.page;
        
        const toShare = {
            title: aTitle,
            url: root + page + "?" + aUrl.replace(/t=\d+\&/i, ""),
            text: aTitle + "\n" + aText.replaceAll("<br>", "\n").substr(0, 100) + "..."
        };
        
        //alert(`Title: ${aTitle}\n${aUrl}\n${cId}\n${aText}`);
        navigator.share(toShare);
        //alert(JSON.stringify(toShare));
    }

    const params = new URLSearchParams(window.location.search);
    const query = params.get("q");

    if (query) {
        openSearch(false);
        const searchBox = document.getElementById("searchBox");
        searchBox.value = query;
        searchText(query);
    }
});

function createSearchUI() {
    // Search button
    const searchButton = document.createElement("button");
    searchButton.id = "openSearchButton";
    searchButton.textContent = "Search"
    searchButton.addEventListener("click", openSearch);

    // Search panel
    const panel = document.createElement("div");
    panel.id = "searchPanel";
    panel.style.display = "none";

    const searchBox = document.createElement("input");
    searchBox.id = "searchBox";
    searchBox.type = "text";
    searchBox.placeholder = "Search this page...";
    
    const searchCount = document.createElement("span");
    searchCount.id = "searchCount";

    const prevButton = document.createElement("button");
    prevButton.textContent = "◀";

    const nextButton = document.createElement("button");
    nextButton.textContent = "▶";

    const closeButton = document.createElement("button");
    closeButton.textContent = "✕";

    prevButton.addEventListener("click", prevMatch);
    nextButton.addEventListener("click", nextMatch);
    closeButton.addEventListener("click", closeSearch);

    searchBox.addEventListener("input", e => {
        clearTimeout(debounceTimer);

        debounceTimer = setTimeout(() => {
            searchText(e.target.value.trim());
        }, 300);
    });

    panel.append(
        searchBox,
        searchCount,
        prevButton,
        nextButton,
        closeButton
    );

    /*
    const container = document.createElement("div");
    container.id = "searchContainer";
    container.append(searchButton, panel);
    */

    document.body.prepend(searchButton, panel);
}

function openSearch(getFocus = tue) {
    const panel = document.getElementById( "searchPanel" );
    panel.style.display = "flex";

    if(getFocus)
      document.getElementById( "searchBox" ).focus();
}

function closeSearch() {
    document.getElementById("searchPanel").style.display = "none";

    const searchBox = document.getElementById("searchBox");
    searchBox.value = "";

    clearHighlights();
}

function escapeRegExp(text) {
    return text.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
}

function searchText(query) {
    clearHighlights();

    if (!query) return;

    const content = document.getElementById("article-wrapper");
    //const content = document.querySelector(".page");
    if (!content) return;

    const regex = new RegExp(escapeRegExp(query), "gi");

    const walker = document.createTreeWalker(
        content,
        NodeFilter.SHOW_TEXT,
        {
            acceptNode(node) {
                if (!node.nodeValue.trim()) {
                    return NodeFilter.FILTER_REJECT;
                }

                // Don't search inside existing highlights
                if (
                    node.parentElement &&
                    node.parentElement.classList.contains("highlight")
                ) {
                    return NodeFilter.FILTER_REJECT;
                }

                return NodeFilter.FILTER_ACCEPT;
            }
        }
    );

    const textNodes = [];

    while (walker.nextNode()) {
        textNodes.push(walker.currentNode);
    }

    textNodes.forEach(textNode => {
        const text = textNode.nodeValue;

        regex.lastIndex = 0;

        if (!regex.test(text)) {
            return;
        }

        const fragment = document.createDocumentFragment();

        let lastIndex = 0;

        regex.lastIndex = 0;

        let match;

        while ((match = regex.exec(text)) !== null) {
            const before = text.slice(lastIndex, match.index);

            if (before) {
                fragment.appendChild(
                    document.createTextNode(before)
                );
            }

            const span = document.createElement("span");
            span.className = "highlight";
            span.textContent = match[0];

            fragment.appendChild(span);

            lastIndex = match.index + match[0].length;
        }

        const after = text.slice(lastIndex);

        if (after) {
            fragment.appendChild(
                document.createTextNode(after)
            );
        }

        textNode.parentNode.replaceChild(
            fragment,
            textNode
        );
    });

    matches = document.querySelectorAll(".highlight");

    if (matches.length) {
        currentIndex = 0;
        scrollToMatch(currentIndex);
    } else {
        showCount(0, 0);
    }
}

function clearHighlights() {
    document.querySelectorAll(".highlight").forEach(span => {
        span.replaceWith(
            document.createTextNode(span.textContent)
        );
    });

    // Merge adjacent text nodes created by highlighting
    document.getElementById("article-wrapper")?.normalize();

    matches = [];
    currentIndex = -1;
    showCount();
}

function showCount(value, count) {
    const searches = document.getElementById("searchCount");
    if(searches) {
        searches.innerText = (value === undefined ? '' :`${value}/${count}`);
    }
}

function scrollToMatch(index) {
    /*
    const searches = document.getElementById("searchCount");
    if(searches) {
        searches.innerText = `${index + 1}/${matches.length}`;
    }
    */
    showCount(index + 1, matches.length);
    
    matches.forEach(el => el.classList.remove("active"));

    const match = matches[index];
    match.classList.add("active");

    match.scrollIntoView({
        behavior: "smooth",
        block: "center"
    });
}

function nextMatch() {
    if (!matches.length) return;

    currentIndex = (currentIndex + 1) % matches.length;
    scrollToMatch(currentIndex);
}

function prevMatch() {
    if (!matches.length) return;

    currentIndex =
        (currentIndex - 1 + matches.length) % matches.length;

    scrollToMatch(currentIndex);
}

function injectStyles() {
    const style = document.createElement("style");

    style.textContent = `
        #searchContainer,
        #searchPanel
        {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 8px;
            background: white;
            border-bottom: 1px solid #ddd;
            display: flex;
            gap: 8px;
            align-items: center;
            justify-content: space-between;
        }

        #searchPanel {
            z-index: 1500;
            height: 3rem;
        }
        
        #searchPanel button {
            width: 2rem;
            height 2rem;
        }
        
        #openSearchButtonxx {
            position: fixed;
            top: 0.5rem;
            left: 0.5rem;
        }

        #searchBox {
            min-width: 180px;
            flex-grow: 1;
            border: none;
            outline: none;
            font-size: 125%;
        }

        .highlight {
            background: yellow;
        }

        .highlight.active {
            background: orange;
        }
    `;

    document.head.appendChild(style);
}