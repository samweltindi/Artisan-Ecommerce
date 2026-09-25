<?php
session_start();
// Offline/"pay later" ordering has been removed - M-Pesa is now the only
// payment path. This file is kept only so any old bookmark/link doesn't
// hit a dead 404; it just forwards into the real checkout flow.
header("Location: delivery_address.php");
exit;