<?php

/**
 * Linna App.
 *
 * @author Sebastian Rapetti <sebastian.rapetti@alice.it>
 * @copyright (c) 2018, Sebastian Rapetti
 * @license http://opensource.org/licenses/MIT MIT License
 */
declare(strict_types=1);

namespace App\Models;

use Linna\Mvc\Model;

/**
 * Error Page Model.
 */
class ErrorModel extends Model
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Raise error.
     *
     * @param int       $statusCode
     * @param string    $description
     *
     * @return void
     */
    public function raiseError(int $statusCode, string $description): void
    {
        $this->set(['code' => $statusCode, 'description' => $description]);
    }
}
