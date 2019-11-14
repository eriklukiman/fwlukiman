<?php
namespace Lukiman\Cores;

use \Lukiman\Cores\Interfaces\Database\{Basic, Transaction, Operation};

class Database extends Database\Base implements Basic, Transaction, Operation {
}
