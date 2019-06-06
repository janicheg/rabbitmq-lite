<?php

namespace SlimQ;

/**
 * Interface ConsumerInterFace
 * @package SlimQ
 */
interface ConsumerInterFace
{
    public function run($json);
}