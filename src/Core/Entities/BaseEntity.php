<?php
namespace Core\Entities;

use Symfony\Component\HttpFoundation\Request;

class BaseEntity{
	protected $errors = array();
	public function __construct(){
			
	}
	public function bind2($arr){		
        if(!is_array($arr))
            $arr = get_object_vars($arr);
            
		$properties = get_object_vars($this);
        foreach($properties as $key => $value){
			if($key!='errors'){
				if(array_key_exists($key, $arr)){
					$val = $arr[$key];					
					if(is_string($val)){
						$this->{$key} = $this->convert_smart_quotes($val);
					}else{
						$this->{$key} = $val;
					}
				}
			}
        }
        return $this;
    }
    
	public function bind($properties){
		if(!is_array($properties)){
			if(is_string($properties)){
				//var_dump($properties); 
				$properties  = [];
			}else{				
				$properties = get_object_vars($properties);
			}
		}
		if(is_array($properties)){
		    foreach($properties as $key => $value){
			    if(is_string($value)){
				$this->{$key} = $this->convert_smart_quotes($value);
			    }else{
				$this->{$key} = $value;
			    }
		    }
		}	
		
		return $this;	
	}
	public function bindRequest(Request $request){
		
		$properties = get_object_vars($this);
		
		if(is_array($properties)){
			$all = $request->request->all();
		    foreach($properties as $key => $value){
			    if($key!='errors'){
				    //$varx = $request->request->get($key);
				    $varx = isset($all[$key]) ? $all[$key] : NULL;
				    if(is_string($varx)){
						$varx = trim($this->convert_smart_quotes($varx));
				    }
				    $this->{$key} = $varx;
			    }
		    }
		}
		
		return $this;	
	}
	public function has_error(){
		if (count($this->errors)>0)
			return TRUE;
		return FALSE;
	}
	public function error_messages(){
		//return implode('<br />',array_values($this->errors));
		//return var_export($this->errors);
		$text = '';
        $keys = array_keys($this->errors);
        foreach ($keys as $key) {
	    $keyx = str_replace('\' ', '\'', ucwords(str_replace('\'', '\' ', strtolower($key))));
            $text .= /*$keyx.' '.*/$this->errors[$key].'<br>';
            //$text .= $this->errors[$key].'<br/>';
        }	
        return $text;
	}
	public function error_keys(){
		return array_keys($this->errors);
	}
	public function add_error($key,$msg){
		if(array_key_exists($key,$this->errors))
			$this->errors[$key] .= $msg .'<br>';	
		else
			$this->errors[$key] = $msg;	
	}
	protected function required($props){
		foreach($props as $key){
			if(!isset($this->{$key})){
				$str = preg_replace('/([a-z])([A-Z])/', '$1 $2', ucfirst($key));
				$str = str_replace('_',' ',$str);
				$this->add_error($key,$str.' is empty');
			}
		}	
	}
    protected function requiredNotEmpty($props){
        foreach($props as $key){
            if(!isset($this->{$key}) || (isset($this->{$key}) && empty($this->{$key}))){
                $str = preg_replace('/([a-z])([A-Z])/', '$1 $2', ucfirst($key));
                $str = str_replace('_',' ',$str);
                $this->add_error($key,$str.' is empty');
            }
        }
    }
    
    //https://stackoverflow.com/a/1262060/1225672
    protected function convert_smart_quotes($string) 
	{ 
		//$string = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $string);
		//$string = preg_replace( '/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $string );
		

		$search = array(chr(145), 
				chr(146), 
				chr(147), 
				chr(148), 
				chr(151),
				'`'
				); 

		$replace = array("'", 
				 "'", 
				 '"', 
				 '"', 
				 '-',
				 '\''); 

		$string = str_replace($search, $replace, $string); 
		$string = $this->clean_string($string);
		// Remove control characters
		$string = $this->remove_invisible_characters($string);

		return $string;
	}
	//codeigniter core/security.php
	function _is_ascii($str)
	{
		return (preg_match('/[^\x00-\x7F]/S', $str) == 0);
	}
	function clean_string($str)
	{
		if ($this->_is_ascii($str) === FALSE)
		{
			$str = @iconv('UTF-8', 'UTF-8//IGNORE', $str);
		}

		return $str;
	}
	 /**
     * Remove Invisible Characters
     *
     * This prevents sandwiching null characters
     * between ascii characters, like Java\0script.
     *
     * @access	public
     * @param	string
     * @return	string
     */
    function remove_invisible_characters($str, $url_encoded = true)
    {
        $non_displayables = array();
        if ($url_encoded) {
            $non_displayables[] = "/%0[0-8bcef]/";
            $non_displayables[] = "/%1[0-9a-f]/";
        }
        $non_displayables[] = "/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F\\x7F]+/S";
        do {
            $str = preg_replace($non_displayables, "", $str, -1, $count);
        } while ($count);
        return $str;
    }
}

