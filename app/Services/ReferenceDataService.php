<?php

namespace App\Services;

use App\Models\Klien;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ReferenceDataService
{
    public static function getKliens()
    {
        return Cache::tags(['ref'])->remember('ref:kliens', 7200, function () {
            return Klien::orderBy('nama')->get();
        });
    }

    public static function getSuppliers()
    {
        return Cache::tags(['ref'])->remember('ref:suppliers', 7200, function () {
            return Supplier::orderBy('nama')->get();
        });
    }

    public static function getPurchasingUsers()
    {
        return Cache::tags(['ref'])->remember('ref:purchasing_users', 7200, function () {
            return User::whereIn('role', ['manager_purchasing', 'staff_purchasing', 'direktur'])
                ->orderBy('nama')
                ->get();
        });
    }
}
