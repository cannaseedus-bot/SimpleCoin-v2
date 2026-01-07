#!/bin/bash
php tests/test_products.php || exit 1
php tests/test_cart_math.php || exit 1
echo "🎉 All PHP tests passed!"
