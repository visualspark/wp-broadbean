<?php
	/******************************************
	 Template engine class (use {{tag}} tags in templates
	 *******************************************/
    class Template {
    	/******************************************
    	 The filename of the template to load.
    	 *******************************************/
        protected $file;
        
        /******************************************
         An array of values for replacing each tag 
		 on the template (the key for each value is its corresponding tag).
         *******************************************/
        protected $values = array();
        
        /******************************************
         Creates a new Template object and sets 
		 its associated file.
         *******************************************/
        public function __construct($file) {
            $this->file = $file;
        }
        
        /******************************************
         Sets a value for replacing a specific tag.
         *******************************************/
        public function set($key, $value) {
            $this->values[$key] = $value;
        }
        
        /******************************************
         Outputs the content of the template, 
		 replacing the keys for its respective values.
         *******************************************/
        public function output() {
        	/******************************************
        	 Tries to verify if the file exists.
        	 If it doesn't return with an error message.
        	 Anything else loads the file contents 
			 and loops through the array replacing every key for its value.
        	 *******************************************/
            if (!file_exists($this->file)) {
            	return "Error loading template file ($this->file).<br />";
            }
            $output = file_get_contents($this->file);
            
            foreach ($this->values as $key => $value) {
            	$tagToReplace = "{{{$key}}}";
            	$output = str_replace($tagToReplace, $value, $output);
            }

            return $output;
        }
        
        /******************************************
         Merges the content from an array of templates and separates it with $separator.
         *******************************************/
        static public function merge($templates, $separator = "\n") {
        	/******************************************
        	 Loops through the array concatenating the outputs 
			 from each template, separating with $separator.
        	 If a type different from Template is found we provide an error message. 
        	 *******************************************/
            $output = "";
            
            foreach ($templates as $template) {
            	$content = (get_class($template) !== "Template")
            		? "Error, incorrect type - expected Template."
            		: $template->output();
            	$output .= $content . $separator;
            }
            
            return $output;
        }
    }

?>