<?php declare(strict_types=1); // strict requirement

class Category {

	public ?int $Id;
	public ?string $Name;
	public ?string $CssClass;
	public ?bool $IsCampaign;
    public ?bool $IsActive;
    public ?string $Tagline;

	public function __construct() {
		$this->Id = null;
		$this->Name = "";
		$this->CssClass = "";
		$this->IsCampaign = false;
        $this->IsActive = false;
        $this->Tagline = null;
	}

	public function FromRow(array $row)
	{
		//echo $row["CategoryId"].", ".$row["CategoryName"].", ".$row["IsCampaign"].", ".$row["OrderIndex"];
		//echo "<br/>";

		$this->Id = (int)$row["CategoryId"];
		$this->Name = $row["CategoryName"];
		$this->CssClass = $row["CategoryClass"];
		$this->IsCampaign = ($row["IsCampaign"] == 1);
        $this->IsActive = ($row["IsActive"] == 1);
        $this->Tagline = $row["Tagline"];
    }
}

class CategoryBus {

	private mysqli $conn;

	public function __construct(mysqli $conn) {
		$this->conn = $conn;
	}

	public function Add( Category $category ) : int
	{
		// prepare and bind
		$sql = "INSERT INTO Category (CategoryName, CategoryClass, Tagline, IsCampaign) VALUES ( ?, ?, ?, ? )";
		
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("sssi", $name, $cssClass, $tagline, $isCampaign);

		// set parameters and execute
		$name = $category->Name;
		$cssClass = $category->CssClass;
        $tagline = $category->Tagline;
		$isCampaign = ( $category->IsCampaign ? 1 : 0 );
		
		$result = $stmt->execute();
		$categoryId = $stmt->insert_id;
		
		$stmt->close();
		
		return $categoryId;
	}
	
	public function Edit( Category $category ) : int
	{
        if( $category->IsActive ) {
            $this->ResetIsActive();
        }
        
		// prepare and bind
		$sql = "UPDATE Category SET ";
		$sql .= "CategoryName = ?, Tagline = ?, IsCampaign = ?, IsActive = ? ";
		$sql .= "WHERE CategoryID = ?";
		
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("ssiii", $name, $tagline, $isCampaign, $isActive, $categoryId);

		// set parameters and execute
		$name = $category->Name;
        $tagline = $category->Tagline;
		$isCampaign = ( $category->IsCampaign ? 1 : 0 );
        $isActive = ( $category->IsActive ? 1 : 0 );
		$categoryId = $category->Id;
		
		$result = $stmt->execute();
		$stmt->close();
		
		return $categoryId;
	}

	public function ReadAll() : array
	{
		$sql = "SELECT CategoryId, CategoryName, CategoryClass, IsCampaign, IsActive, Tagline FROM Category";
		$result = $this->conn->query($sql);
		
		$categories = array();
		
		if ($result->num_rows > 0) {

			// output data of each row
			while($row = $result->fetch_assoc()) {
				
				$category = new Category();
				$category->FromRow($row);
				
				array_push($categories, $category);
			}
		  		  
		}
		
		return $categories;
	}

	public function Read(int $id) : Category
	{
		$sql = "SELECT CategoryId, CategoryName, CategoryClass, IsCampaign, IsActive, Tagline FROM Category";
		$sql .= " WHERE CategoryId = $id";
		$result = $this->conn->query($sql);

		$category = new Category();
		
		if ($result->num_rows > 0) {

			while($row = $result->fetch_assoc()) {
				
				$category->FromRow($row);
			}
		}
		
		return $category;
	}
    
    public function ResetIsActive() {
        //$sql = "sql";
        $sql = "UPDATE Category SET IsActive = 0 WHERE IsActive = 1";
        $result = $this->conn->query($sql);
    }
	
	public function UpdateArticleOrder( int $id, array $articleOrder )
	{
		$sql = "UPDATE ArticleCategory SET CategoryOrder=NULL WHERE ArticleID = $id";
		$result = $this->conn->query($sql);
		
		$sql = "UPDATE ArticleCategory SET ";
		$sql .= "CategoryOrder = ? ";
		$sql .= "WHERE CategoryID = ? AND ArticleID = ?";
		
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("iii", $categoryOrder, $categoryId, $articleId);
		
		$categoryId = $id;
		$length = count($articleOrder);
		
		for ($i = 0; $i < $length; $i++) 
		{
			$categoryOrder = $i;
			$articleId = $articleOrder[$i];
			
			$result = $stmt->execute();
		}

		$stmt->close();
	}
	
}

?>