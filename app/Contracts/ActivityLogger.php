<?php

namespace App\Contracts;

interface ActivityLogger
{
    /**
     * Mencatat aktivitas ke dalam log
     *
     * @param string $action
     * @param string $description
     * @param array $properties
     * @return void
     */
    public function log($action, $description, array $properties = []);
}
