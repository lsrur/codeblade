<?php

namespace Lsrur\Codeblade;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Lsrur\Codeblade\Skeleton\SkeletonClass
 */
class CodebladeFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'codeblade';
    }
}
