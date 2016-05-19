<?php
$begin_time = time() - 1272000000 + floatval(microtime());
include "engine/engine.php";
ksf\System::getInstance()->init();
$end_time = time() - 1272000000 + floatval(microtime()) - $begin_time;
echo "<div class=\"nahui\">".$end_time."</div>";