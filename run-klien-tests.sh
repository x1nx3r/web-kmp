#!/usr/bin/env bash

echo "🧪 Running Klien Management Test Suite"
echo "======================================"

# Navigate to project directory
cd /mnt/libraries/proyek/web-kmp

echo ""
echo "1️⃣  Testing Unit Tests (Klien Model)"
echo "-----------------------------------"
./vendor/bin/phpunit tests/Unit/KlienTest.php

echo ""
echo "2️⃣  Testing Basic Controller Functionality"
echo "----------------------------------------"
./vendor/bin/phpunit tests/Feature/KlienControllerTest.php --filter "it_can_display_klien_index_page|index_page_displays_all_kliens|it_can_search_kliens"

echo ""
echo "3️⃣  Testing Smart CRUD Operations"
echo "--------------------------------"
./vendor/bin/phpunit tests/Feature/KlienControllerTest.php --filter "it_can_create_branch_with_smart_logic|it_updates_placeholder_when_first_branch_added|it_can_update_branch_entry|it_can_soft_delete_branch_entry"

echo ""
echo "4️⃣  Testing AJAX Company Operations"
echo "----------------------------------"
./vendor/bin/phpunit tests/Feature/KlienControllerTest.php --filter "it_can_create_new_company_via_ajax|company_creation_prevents_duplicates"

echo ""
echo "5️⃣  Testing Route Accessibility"
echo "------------------------------"
./vendor/bin/phpunit tests/Feature/KlienRouteTest.php --filter "klien_index_route_exists_and_is_accessible|klien_company_routes_exist"

echo ""
echo "✅ Test Suite Complete!"
echo "======================"