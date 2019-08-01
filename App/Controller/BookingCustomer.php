<?php
Namespace App\Controller;

class BookingCustomer
{
  static $customerid = 0;
  public $firstname;
  public $lastname;
  public $email;
  public $pwd;

  public function httpGetRequest()
  {
  }

  public function httpPostRequest()
  {
  }

  public function __contruct($firstname,$lastname,$email,$pwd)
  {
  $this->customerid += 1;

  $this->firstname = $firstname;
  $this->lastname = $lastname;
  $this->email = $email;
  $this->pwd = $pwd;
  }

  public function registerCustomer()
  {
    if ( isset( $_POST['submit'] ) ) {

    $newCustomer = new BookingCustomer($_POST['firstnames'],$_POST['lastname'],$_POST['email'],$_POST['pwd']);
    var_dump($newCustomer);
                                      }
  }
}
