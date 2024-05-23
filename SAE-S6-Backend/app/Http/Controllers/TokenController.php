<?php

// app/Http/Controllers/TokenController.php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class TokenController extends Controller
{
    public function createToken()
    {
        $uuid = Uuid::uuid4()->toString();
        return response()->json(['token' => $uuid]);

    }
}
