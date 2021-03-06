<?php

class Model {

	protected $elementCount;

	public function __construct() {

		$this->db = new Database();
	}

	public function getPostData() {

		if (isset($_POST['submit'])) {

			unset($_POST['submit']);	
		}

		if(!array_filter($_POST)) {
		
			return false;
		}
		else {

			return array_filter(filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
		}
	}

	public function getGETData() {

		if(!array_filter($_GET)) {
		
			return false;
		}
		else {

			return filter_input_array(INPUT_GET, FILTER_SANITIZE_SPECIAL_CHARS);
		}
	}

	public function listSeries() {

		$dbh = $this->db->connect();
		if(is_null($dbh))return null;
		
		$sth = $dbh->prepare('select distinct snum,year from project order by snum');
		$sth->execute();

		$i = 0;
		while($result = $sth->fetch(PDO::FETCH_ASSOC))
		{
			$data[$i] = $result;
	        $i++;
		}
		$dbh = null;
		return $data;
	}

	public function listDepartments() {

		$dbh = $this->db->connect();
		if(is_null($dbh))return null;
		
		$sth = $dbh->prepare('SELECT DISTINCT department FROM project ORDER BY department');
		$sth->execute();

		$i = 0;
		while($result = $sth->fetch(PDO::FETCH_ASSOC))
		{
			$data[$i] = $result;
	        $i++;
		}
		$dbh = null;
		return $data;
	}

	public function listColleges() {

		$dbh = $this->db->connect();
		if(is_null($dbh))return null;
		
		$sth = $dbh->prepare('SELECT DISTINCT college FROM project ORDER BY college');
		$sth->execute();

		$i = 0;
		while($result = $sth->fetch(PDO::FETCH_ASSOC))
		{
			$data[$i] = $result;
	        $i++;
		}
		$dbh = null;
		return $data;
	}

	public function getCurrentIssue($journal = DEFAULT_JOURNAL) {

		$this->db = new Database();
		$dbh = $this->db->connect($journal);
		if(is_null($dbh))return null;
		
		// Online issues are to filtered from appearing as current issues	
		$sth = $dbh->prepare('SELECT DISTINCT volume, issue, year, month from ' . METADATA_TABLE . ' WHERE issue != \'online\' ORDER BY volume DESC, issue DESC LIMIT 1');
		$sth->execute();
		
		$result = $sth->fetch(PDO::FETCH_OBJ);
		return $result;
	}

	public function preProcessPOST ($data) {

		return array_map("trim", $data);
	}

	public function encrypt ($data) {

		return sha1(SALT.$data);
	}
	
	public function sendLetterToPostman ($fromName = SERVICE_NAME, $fromEmail = SERVICE_EMAIL, 
		$toName = SERVICE_NAME, $toEmail = SERVICE_EMAIL, $subject = 'Bounce', 
		$message = '', $successMessage = 'Bounce', $errorMessage = 'Error') {

	    $mail = new PHPMailer();
        $mail->isSendmail();
        $mail->isHTML(true);
        $mail->setFrom($fromEmail, $fromName);
        $mail->addReplyTo($fromEmail, $fromName);
        $mail->addAddress($toEmail, $toName);
        $mail->Subject = $subject;
        $mail->Body = $message;
        
        return $mail->send();
 	}

 	public function bindVariablesToString ($str = '', $data = array()) {

 		unset($data['count(*)']);
	    
	    while (list($key, $val) = each($data)) {
	    
	        $str = preg_replace('/:'.$key.'/', $val, $str);
		}
	    return $str;
 	}

 	public function listFiles ($path = '') {

 		if (!(is_dir($path))) return array();

 		$files = scandir($path);
 
 		unset($files[array_search('.', $files)]);
 		unset($files[array_search('..', $files)]);
 
 		return $files;
 	}

	public function removeDiacrtics($aliasword) {

		$aliasword = str_replace('??', 'A', $aliasword);
		$aliasword = str_replace('??', 'a', $aliasword);
		$aliasword = str_replace('??', 'S', $aliasword);
		$aliasword = str_replace('??', 's', $aliasword);
		$aliasword = str_replace('??', 'U', $aliasword);
		$aliasword = str_replace('??', 'u', $aliasword);
		$aliasword = str_replace('???', 'S', $aliasword);
		$aliasword = str_replace('???', 's', $aliasword);
		$aliasword = str_replace('??', 'I', $aliasword);
		$aliasword = str_replace('??', 'i', $aliasword);
		$aliasword = str_replace('???', 'N', $aliasword);
		$aliasword = str_replace('???', 'n', $aliasword);
		$aliasword = str_replace('???', 'R', $aliasword);
		$aliasword = str_replace('???', 'r', $aliasword);
		$aliasword = str_replace('???', 'T', $aliasword);
		$aliasword = str_replace('???', 't', $aliasword);
		$aliasword = str_replace('???', 'N', $aliasword);
		$aliasword = str_replace('???', 'n', $aliasword);
		$aliasword = str_replace('???', 'D', $aliasword);
		$aliasword = str_replace('???', 'd', $aliasword);
		$aliasword = str_replace('???', 'M', $aliasword);
		$aliasword = str_replace('???', 'm', $aliasword);
		$aliasword = str_replace('??', 'N', $aliasword);
		$aliasword = str_replace('??', 'n', $aliasword);
		$aliasword = str_replace('???', 'H', $aliasword);
		$aliasword = str_replace('???', 'h', $aliasword);
		$aliasword = str_replace('???', 'L', $aliasword);
		$aliasword = str_replace('???', 'l', $aliasword);
		$aliasword = str_replace('???', 'R', $aliasword);
		$aliasword = str_replace('???', 'r', $aliasword);

		return $aliasword;
	}

	public function extractDetailsFromDescription($word) {
		
		// var_dump($word);
        $xml = simplexml_load_string($word['description']);
        $head = $xml->head;
        $note = $head->note;

        $word['description'] = $xml->description->asXML();
        $word['description'] = preg_replace('/<\/*description>/', '', $word['description']);

        $word['alias'] = (isset($head->alias)) ? (String) $head->alias : '';
        $word['wordNote'] = (sizeof($note) > 1) ? (String) $note[0] : (String) $note;
        $word['aliasNote'] = (sizeof($note) > 1) ? (String) $note[1] : '';

		return $word;
	}

	public function xmlToHtml($html, $word) {

		$this->elementCount = 0;

		// Reform refs
		$html = str_replace('<ref href="', '<a href="' . BASE_URL . 'describe/word/', $html);
		$html = str_replace('</ref>', '</a>', $html);

		// Handle figures
		$html = preg_replace_callback('/(<figure>|<figure src="(.*?)">)(<figcaption\/>|<figcaption>(.*?)<\/figcaption>)<\/figure>/', function ($matches) use($word) {
			
			$caption = (isset($matches[4])) ? $matches[4] : $word;
			$suffix = (++$this->elementCount > 1) ? '_' . $this->elementCount : '';
			$figSrc = (isset($matches[2]) && $matches[2]) ? $matches[2] : $word . $suffix . '.jpg';
        	
        	$figHtml = '';
        	$figHtml .= '<figure class="figure"><img class="img-fluid" data-original="' . PUBLIC_URL . 'images/main/' . $figSrc . '" src="' . PUBLIC_URL . 'images/thumbs/' . $figSrc . '" alt="' . $caption . '">';

			if(isset($matches[4])) $figHtml .=	'<figcaption class="figure-caption">' . $caption . '</figcaption>';

			$figHtml .=	'</figure>';
			return $figHtml;

    	}, $html);

		// Handle aside elements
		$this->elementCount = 1;

		$html = preg_replace_callback('/<aside>(.*?)<\/aside>/', function ($matches) {

        	return '
        		<sup><a tabindex="' . $this->elementCount . '" class="footNote" data-toggle="popover" data-content="' . $matches[1] . '">' . $this->elementCount++ . '</a></sup>';

    	}, $html);

		return $html;
	}
}

?>