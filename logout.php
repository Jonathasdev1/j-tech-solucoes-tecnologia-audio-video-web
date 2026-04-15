<?php
require_once 'includes/session.php';

logout_user();

header('Location: index.php?mensagem=' . urlencode('Logout realizado com sucesso.'));
exit;
