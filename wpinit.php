<?php
class WPInit{

	private function check(){
		if(basename(__FILE__) !== basename($_SERVER["SCRIPT_FILENAME"])){
			die("WPInit can not be called as a dependency.");
		}
		if(php_sapi_name() !== 'cli'){
			die("WPInit is a CLI wizard, thus it can't be used from server.");
		}
	}

	private function load_globals(){ 
		$path = dirname(__FILE__). DIRECTORY_SEPARATOR ."userData.ini";
		
		if(file_exists($path)){
		$userArray = parse_ini_file($path);
		$this->author_name = $userArray['name'];
		$this->author_uri = $userArray['url'];
		}
		else{
			$this->author_name = '';
			$this->author_uri = '';
		}
	}

	private function save_globals(){
		$settings = [
			'name' => $this->author_name,
			'url' => $this->author_uri
		];
		$fp = fopen("userData.ini", "w");
		fwrite($fp, "name = $settings[name]\nurl = $settings[url]\n");
		fclose($fp);
	}

	private function ask($question, $default =''){
		printf("%s",$question);
		$rs = stream_get_line(STDIN, 1024, PHP_EOL);

		while (empty($default) && empty($rs)) {
			printf("This field is required!\n%s",$question);
			$rs = stream_get_line(STDIN, 1024, PHP_EOL);
		}
		return $rs;
	}

	private function sanitize_name(){
		$this->sanitized_name = mb_strtolower($this->name, mb_detect_encoding($this->name));
		$this->sanitized_name = str_replace(" ", "_", $this->sanitized_name);
	}

	private function init_folders(){
		$this->sanitize_name();
		$string = sprintf("/**\n * %s\n *\n * @package     %s\n * @author      %s\n * @copyright   ".date("Y")." %s\n * @license     %s\n *\n * @wordpress-plugin\n * Plugin Name: %s\n * Plugin URI:  %s\n * Description: %s\n * Version:     %s\n * Author:      %s\n * Author URI:  %s\n * Text Domain: %s\n * License:    %s\n",
			$this->name,$this->sanitized_name,$this->author_name,$this->author_name, $this->license, $this->name, $this->URI, $this->description,$this->version, $this->author_name, $this->author_uri, $this->sanitized_name, $this->license);

		mkdir($this->sanitized_name);
		chdir($this->sanitized_name);
		$pointer = fopen($this->sanitized_name.'.php',"w");
		fwrite($pointer, $string);
		fclose($pointer);
	}

	public function __construct(){
		
		$this->check();
		$this->load_globals();

		$ask_author_name = !empty(trim($this->author_name)) ? 
					"Author Name
					(Press enter for just leaving it as ".$this->author_name."):"
					: "Author Name of the plugin:";

		$ask_author_uri = !empty(trim($this->author_uri)) ?
					"Author URI
					(Press enter for just leaving it as ".$this->author_uri."):"
					: "Author URI of the plugin:";

		$this->name = $this->ask("Name of the plugin:");
		$this->version = $this->ask("Version of the plugin:");

		$an = $this->ask($ask_author_name, $this->author_name);
		$au = $this->ask($ask_author_uri, $this->author_uri);

		if($an != $this->author_name && !empty(trim($an))){
			$this->author_name = $an;
		}
		if($au != $this->author_uri && !empty(trim($au))){
			$this->author_uri = $au;
		}

		$this->save_globals();

		$this->version = $this->ask("Version of the plugin:");
		$this->description = $this->ask("Description of the plugin:");
		$this->license = $this->ask("License of the plugin:");
		$this->network = $this->ask("Network of the plugin:");
		$this->URI = $this->ask("URI of the plugin:"); 
		$this->dependencies = $this->ask("Do you wish to include dependencies?");

		$this->init_folders();
	}
}
new WPInit();
 ?>