<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Kreait\Firebase;
use Kreait\Firebase\Factory;
use App\Models\Image; // Assurez-vous d'avoir un modèle Image

class ImageController extends Controller
{
    protected $storage;

    public function __construct()
    {

        $firebase = (new Factory)
        ->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')))
        ->withDatabaseUri(env("FIREBASE_DATABASE_URL"));

        $this->storage = $firebase->createStorage();
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        $file = $request->file('image');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = 'images/' . $fileName;

        // Upload file to Firebase Storage
        $bucket = $this->storage->getBucket();
        $object = $bucket->upload(
            file_get_contents($file->getRealPath()),
            [
                'name' => $filePath
            ]
        );

        // Get the URL of the uploaded file

        $url = $object->signedUrl(new \DateTime('tomorrow'));

        $url = $object->signedUrl();

        // Save URL in the database
        return $url    ;
    }


    public function generateSignedUrl($url)
    {
        $urlComponents = parse_url($url);
        $objectPath = ltrim($urlComponents['path'], '/');

        $bucket = $this->storage->getBucket();
        $object = $bucket->object($objectPath);

        // Generate a new signed URL
        $signedUrl = $object->signedUrl(new \DateTime('+20 minutes'));
        return $signedUrl;
    }
}

