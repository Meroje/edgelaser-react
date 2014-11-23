<?php namespace EdgeLaser;

use Datagram\Socket as Client;

class Game
{
    public $id;
    public $state;
    public $name;
    protected $multiplicator = 0;
    protected $color = LaserColor::LIME;

    const STATE_PAUSED = 'paused';
    const STATE_RUNNING = 'running';
    
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->state = self::STATE_PAUSED;
    }

    public function setName($name = '')
    {
        $this->name = substr($name,0,12);
    }

    public function isRunning()
    {
        return $this->id && $this->state == self::STATE_RUNNING;
    }

    public function setResolution($px)
    {
        $this->multiplicator = floor(65535/$px);
    }

    public function setDefaultColor($color)
    {
        $this->color = $color;
    }

    public function addLine($x1, $y1, $x2, $y2, $color = null)
    {
        $m = $this->multiplicator;
        $color = is_null($color) ? $this->color : $color;

        $x1     = pack('v', $x1*$m);
        $y1     = pack('v', $y1*$m);
        $x2     = pack('v', $x2*$m);
        $y2     = pack('v', $y2*$m);
        $color  = pack('C', $color);

        $cmd = pack('C', $this->id) . 'L' . $x1 . $y1 . $x2 . $y2 . $color;
        $this->client->send($cmd);

        return $this;
    }

    public function addCircle($x, $y, $diameter, $color = null)
    {
        $m = $this->multiplicator;
        $color = is_null($color) ? $this->color : $color;

        $x = pack('v', $x*$m);
        $y = pack('v', $y*$m);
        $diameter = pack('v', $diameter*$m);
        $color = pack('C', $color);

        $cmd = pack('C', $this->id) . 'C' . $x . $y . $diameter . $color;
        $this->client->send($cmd);

        return $this;
    }

    public function addRectangle($x1, $y1, $x2, $y2, $color = null)
    {
        $m = $this->multiplicator;
        $color = is_null($color) ? $this->color : $color;

        $x1     = pack('v', $x1*$m);
        $y1     = pack('v', $y1*$m);
        $x2     = pack('v', $x2*$m);
        $y2     = pack('v', $y2*$m);
        $color  = pack('C', $color);

        $cmd = pack('C', $this->id) . 'D' . $x1 . $y1 . $x2 . $y2 . $color;
        $this->client->send($cmd);

        return $this;
    }
}
