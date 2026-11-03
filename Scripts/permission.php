<?php declare(strict_types=1); // strict requirement

class Permission {

	public ?bool $CanUpdateCategories;
	public ?bool $CanUpdateArticles;
	public ?bool $CanUpdateAllArticles;
	public ?bool $CanPostComments;
	
	public function __construct() 
	{
		$this->CanUpdateCategories = False;
		$this->CanUpdateArticles = False;
		$this->CanUpdateAllArticles = False;
		$this->CanPostComments = False;
	}

	public function FromRow(array $row)
	{
		$this->CanUpdateCategories = ($row["CanUpdateCategories"] == 1);
		$this->CanUpdateArticles = ($row["CanUpdateArticles"] == 1);
		$this->CanUpdateAllArticles = ($row["CanUpdateAllArticles"] == 1);
		$this->CanPostComments = ($row["CanPostComments"] == 1);
	}

	public function ToArray() : array
	{
		$result = array(
			'CanUpdateCategories'=>$this->CanUpdateCategories,
			'CanUpdateArticles'=>$this->CanUpdateArticles,
			'CanUpdateAllArticles'=>$this->CanUpdateAllArticles,
			'CanPostComments'=>$this->CanPostComments
		);
		return $result;
	}
}

class PermissionBus {

	private mysqli $conn;

	public function __construct(mysqli $conn) {
		$this->conn = $conn;
	}
	
	public function ReadForPlayer(int $playerId) : Permission
	{
		$sql = "SELECT P.PlayerID, R.CanUpdateCategories, R.CanUpdateArticles, R.CanUpdateAllArticles, R.CanPostComments ";
		$sql .= "FROM Player P ";
		$sql .= "INNER JOIN Role R ON R.RoleID = P.RoleID ";
		$sql .= "WHERE P.PlayerID = $playerId";
		
		$result = $this->conn->query($sql);		
		$perm = new Permission();
		
		if ($result->num_rows > 0) 
		{
			$row = $result->fetch_assoc();
			$perm->FromRow($row);
		}
		
		return $perm;
	}
}


?>