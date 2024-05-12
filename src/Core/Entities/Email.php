<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Core\Entities;
/**
 * Description of Email
 *
 * @author aldo
 */
class Email {
    public $fromName;
    public $fromEmail;
    public $toEmail;
    public $toName;
    public $subject;
    public $htmlMessage;
    public $plainTextMessage;
    public $headers;

    public $cc = []; //format array: [{'email':'saa@aa.com', 'name':'saa'}]
    public $bcc = []; //format array: [{'email':'saa@aa.com', 'name':'saa'}]

}
