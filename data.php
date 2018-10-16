<?php

require( "dbconnect.php" );

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

	public function curlGet($path, $filter) {

		$wsseHeader[] = "Content-Type: application/vnd.api+json";
		$wsseHeader[] = $this->getHeader();
		$options = array(
			CURLOPT_URL => $this->_url . $path . $filter,
			CURLOPT_HTTPHEADER => $wsseHeader,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER => false
		);

		debug($this->_url . $path . $filter);
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

	public function get($path, $filter) {
		$response = $this->curlGet($path, $filter);
		return $response;
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

class CallsAttributes {

	public $subject;
	public $phoneNumber;

	public function __construct($subject, $phoneNumber) {
		$this->subject = $subject;
		$this->phoneNumber = $phoneNumber;
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

class CallsRelationships {
	public $callStatus;
	public $direction;

	public function addStatus($id) {
		$this->callStatus = [
			'data' => [
				'type' => 'callstatuses',
				'id' => $id
			]
		];
	}

	public function addDirection($id) {
		$this->direction = [
			'data' => [
				'type' => 'calldirections',
				'id' => $id
			]
		];
	}

	public function addActivityTargets($leadId) {
		$this->activityTargets = [
			'data' => [
				[
					'type' => 'leads',
					'id' => $leadId
				]
			]];
	}
}

function debug($data) {
	echo "<pre>";
	print_r($data);
	echo "</pre>";
}

/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

$leadId = $_POST['lead_id'];
$companyId = $_POST['company_id'];
$status = $_POST['status'];
$userName = 'dev';
$userApiKey = '3dc80aa0c30f554de82af4ab3924d37316a998cc';
$url="http://oro.demo";

// Change status for "Anketa"!!!
if ($status == 'TS') {

	$stmt = "SELECT * FROM vicidial_list WHERE lead_id = $leadId";
	$res = mysql_query($stmt);
	$data = mysql_fetch_array($res);
	debug($data);

	$stmt = "SELECT campaign_name FROM vicidial_campaigns WHERE campaign_id = $companyId";
	$res = mysql_query($stmt);
	$company = mysql_fetch_row($res);

	if ($data['last_name'] == null) {
		$data['last_name'] = 'null';
	}
	if ($data['email'] == null) {
		$data['email'] = rand(10000,20000).'@mail.ru';
	}

	// Add new Lead
	$attributes = new LeadsAttributes(
		$company[0],
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
	debug($resp);

	// Get lead id
	$crm = new OroRequest($url, $userName, $userApiKey);
	$response = $crm->get('/index.php/api/leads?filter[phones]=', $data['phone_number']);
	$arr = (array) json_decode($response);
	debug($arr);
	echo $arr['data'][0]['id'];

	// Add new Call
	/*$attrs = new CallsAttributes('Test', $data['phone_number']);
	$relationships = new CallsRelationships;
	$relationships->addStatus('completed');
	$relationships->addDirection('outgoing');
	$relationships->addActivityTargets($arr['data'][0]['id']);
	$call = new NewEntities('calls', $attrs, $relationships);
	$crm = new OroRequest($url, $userName, $userApiKey);
	$resp = $crm->post('/index.php/api/calls', $call);
	debug($call);
	debug($resp);*/

}

