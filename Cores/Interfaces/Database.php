<?php
namespace Lukiman\Cores\Interfaces;

use \Lukiman\Cores\Interfaces\Database\{Basic, Operation, Transaction};

interface Database extends Basic, Operation, Transaction {}
