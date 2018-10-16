<?php

class Request {

	protected $_url;
	protected $_username;
	protected $_apiKey;

	public function __construct($url, $username, $apiUserKey) {
		$this->_url = $url;
		$this->_username = $username;
		$this->_apiKey = $apiUserKey;
	}

	public function getHeader() {
		$nonce = hash_hmac('sha512', uniqid(null, true), uniqid(), true);
		$created = new \DateTime('now', new \DateTimezone('UTC'));
		$created = $created->format(\DateTime::ISO8601);
		$digest = sha1($nonce . $created . $this->_apiKey, true);
		return sprintf(
			'X-WSSE: UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"', $this->_username, base64_encode($digest), base64_encode($nonce), $created
		);
	}

	public function curlPost($path, $data = array()) {

		$wsseHeader[] = "Content-Type: application/vnd.api+json";
		$wsseHeader[] = $this->getHeader();
		$options = array(
			CURLOPT_URL => $this->_url . $path,
			CURLOPT_HTTPHEADER => $wsseHeader,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER => false,
			CURLOPT_CUSTOMREQUEST => 'POST'
		);
		print_r($options[CURLOPT_URL]);
		if (isset($data)) {
			$options += array(
				CURLOPT_POSTFIELDS => $data,
				CURLOPT_SAFE_UPLOAD => true
			);
		}

		$ch = curl_init();
		curl_setopt_array($ch, $options);
		$result = curl_exec($ch);


		if (false === $result) {
			echo curl_error($ch);
		}

		curl_close($ch);
		return $result;
	}
}

class OroRequest extends Request{

	public function __construct($url ,$u_name, $apikey) {
		parent::__construct($url ,$u_name, $apikey);
	}

	public function post($path, $data) {
		$resp=$this->curlPost($path, json_encode(['data'=>$data]));
		return $resp;
	}

}

class Entities {

	public $type;
	public $id;

	public function __construct($type, $id) {
		$this->type = $type;
		$this->id = $id;
	}
}

class EmailsEntities {
	public $email;
	public function __construct($email) {
		$this->email=$email;
	}
}

class PhonesEntities{
	public $phone;
	public function __construct($phone) {
		$this->phone=$phone;
	}
}

class NewEntities {
	public $type;
	public $relationships;
	public $attributes;

	public function __construct($type, $attributes , $relationships) {
		$this->type = $type;
		$this->attributes = $attributes;
		$this->relationships = $relationships;
	}

}

class Attributes {

	public $firstName;
	public $lastName;
	public $primaryEmail;
	public $primaryPhone;
	public $emails;
	public $phones;

	public function __construct(
		$firstName, $lastName, $emails, $phones
	) {
		$this->emails       = [ $emails ];
		$this->phones       = [ $phones ];
		$this->firstName    = $firstName;
		$this->lastName     = $lastName;
		$this->primaryEmail = $emails->email;
		$this->primaryPhone = $phones->phone;
	}
}

class LeadsAttributes extends Attributes {

	public $name;

	public function __construct($name, $firstName, $lastName, $emails, $phones) {
		parent::__construct($firstName, $lastName, $emails, $phones);
		$this->name = $name;
	}
}

class Relationships {

	public function addOwner($id) {
		$this->owner=['data'=>new Entities('users',$id)];
	}
	public function addOrganization($id) {
		$this->organization=['data'=>new Entities('organizations',$id)];
	}
}

function debug($data) {
	echo "<pre>";
	print_r($data);
	echo "</pre>";
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require( "dbconnect.php" );
$leadId = $_POST['lead_id'];
$stmt = "SELECT * FROM vicidial_list WHERE lead_id = $leadId";
$res = mysql_query($stmt);
$data = mysql_fetch_array($res);

echo '<pre>';
print_r($data);
echo '</pre>';

$userName = 'dev';
$userApiKey = '3dc80aa0c30f554de82af4ab3924d37316a998cc';
$url="http://oro.demo";

if ($data['last_name'] == null) {
	$data['last_name'] = 'null';
}
if ($data['email'] == null) {
	$data['email'] = rand(10000,20000).'@mail.ru';
}

$attributes = new LeadsAttributes(
	'Mary Jane',
	$data['first_name'],
	$data['last_name'],
	new EmailsEntities($data['email']),
	new PhonesEntities($data['phone_number'])
);
$relationships = new Relationships();
$relationships->addOwner('1');
$relationships->addOrganization('1');
$lead = new NewEntities( 'leads', $attributes, $relationships);
$crm = new OroRequest($url, $userName ,$userApiKey);
$resp=$crm->post('/index.php/api/leads', $lead);

debug($lead);
debug($resp);