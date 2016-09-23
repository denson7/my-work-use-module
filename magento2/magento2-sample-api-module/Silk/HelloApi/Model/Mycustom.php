<?php
/**
 * Created by PhpStorm.
 * User: denson
 * Date: 16-9-23
 * Time: 上午10:52
 */
namespace Silk\HelloApi\Model;

use Silk\HelloApi\Api\MycustomInterface;
/**
 * Implementation class of contract.
 */
class Mycustom implements MycustomInterface {
    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @return string Greeting message with users name.
     */
    public function name($name) {
        return "hello world,this is your first api test:".$name;
    }
}