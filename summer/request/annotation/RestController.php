<?php


namespace summer\request\annotation;

use Attribute;
use summer\annotation\Controller;
use summer\response\annotation\ResponseBody;

#[Attribute(Attribute::TARGET_CLASS)]
#[Controller]
#[ResponseBody]
class RestController
{

}