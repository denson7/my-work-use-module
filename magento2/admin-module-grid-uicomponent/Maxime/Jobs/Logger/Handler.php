<?php
/**
 * Created by PhpStorm.
 * User: denson
 * Date: 16-8-5
 * Time: 下午6:06
 */
namespace Maxime\Jobs\Logger;

use Monolog\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::DEBUG;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/maxime_jobs.log';
}