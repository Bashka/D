<?php
namespace D\library\patterns\structure\identification\test;

use D\library\patterns\structure\identification\OID;
use D\library\patterns\structure\identification\TOID;

class OIDMock implements OID{
  use TOID;
}