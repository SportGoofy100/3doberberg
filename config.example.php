<?php
// Kopiere diese Datei zu "config.php" und trage dort deinen echten Stripe Secret Key ein.
// WICHTIG: "config.php" darf NIEMALS ins Git-Repo committed werden (siehe .gitignore)!
// Du bekommst deinen Secret Key unter https://dashboard.stripe.com/apikeys
// (verwende zum Testen den "sk_test_..." Key, für den echten Betrieb den "sk_live_..." Key)

define('STRIPE_SECRET_KEY', 'sk_test_DEIN_KEY_HIER');

// Basis-URL deiner Seite, ohne abschließenden Slash
define('SHOP_BASE_URL', 'https://3doberberg.de');
