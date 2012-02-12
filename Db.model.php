<?
class Db {
	
	private $connection;
	private $json;
	private $dbh;
	private $config = array(
		'db_host' => 'localhost',
		'db_name' => 'test',
		'db_username' => 'root',
		'db_password' => 'root'
	);

	function __construct($storedProcedure, $stripHTML=true) {
		$this->storedProcedure = $storedProcedure;
		$this->stripHTML = $stripHTML;
		$this->connect();
	}

	private function connect() {
			
		try {
			$dsn = 'mysql:host='.$this->config['db_host'].';dbname='.$this->config['db_name'];
			$this->dbh = new PDO($dsn, $this->config['db_username'], $this->config['db_password']);
			$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->procedures();
		}
		catch(PDOException $e) {
			$this->errorMsg($e);
		}		
	}
	
	private function procedures() {
		foreach($this->storedProcedure as $key=>$val) {
			$this->storedProcedure[$key] = $this->dbh->prepare($val);
		}
	}
	
	private function cleanData($d) {
		$cleaned = ($this->stripHTML ? addslashes(strip_tags($d)) : addslashes($d));
		return $cleaned;
	}
	
	public function insertData($index, $data) {
		$input = $this->cleanData($data);
		try {
			$this->storedProcedure[$index]->execute($input);
			return true;
		}
		catch(PDOException $e) {
			$this->errorMsg($e);
			return false;
		}
	}
	
	public function selectData($index) {
		$sth = $this->dbh->query($this->storedProcedure[$index]->queryString);
		$sth->setFetchMode(PDO::FETCH_OBJ);
		
		return $sth->fetch();
	}
		
	private function errorMsg ($e) {
		return $e->getMessage();
		$page = $_SERVER['PHP_SELF'];
		file_put_contents('PDOErrors.txt', $e->getMessage(), FILE_APPEND);
	}
	
}

/*$d = array('name' => 'Jeff', 'email' => 'jeff@jeff.com', 'descriptions' => 'test 2');
$d2 = array('name' => 'Becky', 'email'=> 'beckster@gmail.com', 'descriptions' => 'Becky the dreamer Im glad you still dream');
$procedure = array(
	'test' => 'insert into test (name, email, descriptions) value(:name, :email, :descriptions)',
	'test2' => 'insert into test (name) value(:name)',
	'select1' => 'select * from test'
);
$bdb = new Abstraction($procedure);
$dt = $bdb->selectData('select1');
var_dump($dt);*/
?>