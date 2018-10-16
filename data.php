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
	public function addStatus($status) {
		$this->status=['data'=>new Entities('leadstatuses',$status)];
	}
	public function addSource($id) {
		$this->source=['data'=>new Entities('leadsources',$id)];
	}
	public function addMethod($id) {
		$this->method=['data'=>new Entities('method',$id)];
	}
	public function addAssignedTo($id) {
		$this->assignedTo=['data'=>new Entities('users',$id)];
	}
	public function addReportsTo($id) {
		$this->reportsTo=['data'=>new Entities('users',$id)];
	}
	public function addAddresses($id) {
		$this->addresses=['data'=>new Entities('leadaddresses',$id)];
	}
	public function addGroups($id) {
		$this->groups=['data'=>new Entities('users',$id)];
	}
	public function addAccounts($id) {
		$this->accounts=['data'=>new Entities('users',$id)];
	}
	public function addDefaultInAccounts($id) {
		$this->defaultInAccounts=['data'=>new Entities('users',$id)];
	}
	public function addPicture($id) {
		$this->picture=['data'=>new Entities('users',$id)];
	}
	public function addContact($id) {
		$this->contact=['data'=>new Entities('users',$id)];
	}
	public function addOpportunities($id) {
		$this->opportunities=['data'=>new Entities('users',$id)];
	}
	public function addCompaigns($id) {
		$this->compaign=['data'=>new Entities('compaigns',$id)];
	}
	public function addCustomer($id) {
		$this->customer=['data'=>new Entities('users',$id)];
	}
	public function addAccount($id) {
		$this->account=['data'=>new Entities('users',$id)];
	}
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

$attributes =new LeadsAttributes(
	'Mary Jane',
	$data['first_name'],
	function ($data) {
		if ($data['last_name'] == null) {
			return 'null';
		} else {return $data['last_name'];}
	},
	new EmailsEntities($data['email']),
	new PhonesEntities($data['phone_number'])
);
$relationships = new Relationships();
$relationships->addOwner('1');
$relationships->addOrganization('1');
$relationships->addAddresses($data['address1']);
$lead = new NewEntities( 'leads', $attributes, $relationships);
$crm = new OroRequest($url, $userName ,$userApiKey);
$resp=$crm->post('/index.php/api/leads', $lead);

debug($lead);
debug($resp);