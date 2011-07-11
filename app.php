<?php

include('lib/fitzgerald.php');

class Application extends Fitzgerald {


	public function __construct($options=array()) {


		$options[mountPoint]="/fitzgerald/public/";


		parent::__construct($options);
	}


public function render($filename,$arr=array()){

$arr[site_title]="xxxx.xxxx";

	return parent::render($filename,$arr);
}


	public function get_index() {


		return $this->render('index');
	}

 

	public function get_form() {

	$data[formhtml] = file_get_contents( $this->root().'views/formtemplate.html' );

	return $this->render("form",$data);

	}


	public function post_form() {

	$this->validate($_POST);
	
		if($this->errors){
			return $this->form_error();
		}else{

			if($_POST[mode]=="send"){return $this->data_post();}
			if($_POST[mode]=="edit"){return $this->form_error();}

			return $this->form_confirm();
		}
	}



	function form_error(){

		include($this->root().'lib/simple_html_dom.php');

		$html = file_get_html( $this->root().'views/formtemplate.html' );

		foreach($_POST as $key =>$value){
			$html->find("input[name=$key]",0)->value = $value;
		}

		if($this->errors){
			foreach($this->errors as $key =>$value){
			$html->find("span[id=$key]",0)->innertext = $value;
			}
		}

		$data[formhtml]=$html;
		return $this->render("form",$data);
	}



	function form_confirm(){

		include($this->root().'lib/simple_html_dom.php');

		$html = file_get_html( $this->root().'views/formtemplate.html' );

		foreach($_POST as $key =>$value){
			$html->find("input[name=$key]",0)->value = $value;
			$html->find("input[name=$key]",0)->type = "hidden";
			$html->find("span[id=$key]",0)->innertext = $value;
		}


		$data[formhtml]=$html;
		return $this->render("confirm",$data);
	}






	function validate($arr){

		foreach($this->mustitems() as $val){
				if(!trim($arr[$val])){$errors[$val]="empty";}
		}

		return $this->errors=$errors;
	}


	function mustitems(){
		return array(fname);
	}

	function data_post(){

	//send mail and insert into db
	
		return  $this->save_data($_POST);
	}


	function save_data($arr){
	
	//どのフォームにも共通でありそうなデータは普通のフィールドを用意しておき、あとはjsonencodeしてjsonencodeというフィールドに入れている。

	$commonfield=array("fname","lname","affiliation","email","tel","cancel");

		foreach($commonfield as $key){
		  if($_POST[$key]){ $allitems[$key]=$val;  }
		}

	$allitems[jsonencode]=json_encode($arr);

	$sql=sprintf("INSERT INTO `registration` (`%s`)VALUES (%s);"
		,implode("` ,`",array_keys($allitems))
		,implode(",",array_fill(1,count($allitems),"?") )
		  );

	return $sql.print_r($allitems,true);
	}

}


$app = new Application(array('layout' => 'layout'));

$app->get('index', 'get_index');

$app->get('form', 'get_form');

$app->post('form', 'post_form');




$app->run();


