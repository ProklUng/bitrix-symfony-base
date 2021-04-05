<?php

namespace Local\ServiceProvider\Interfaces;

/**
 * Interface ServiceProviderContract
 * @package Local\ServiceProvider\Contracts
 */
interface ServiceProviderContract
{
    /**
     * Registers the service dependencies in the application
     *
     * @access  public
     * @return  void
     */
    public function register(): void;
}
