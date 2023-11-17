<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Movie;


class MovieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $client = new Client();
        $url = 'https://api.themoviedb.org/3/discover/movie?api_key=' . env('TMDB_API_KEY') . '&page=' . $request->input('page', 1);

        try {
            $response = $client->get($url);
            $movies = json_decode($response->getBody(), true);

            return response()->json(['movies' => $movies], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $client = new Client();
        $url = 'https://api.themoviedb.org/3/discover/movie?api_key=' . env('TMDB_API_KEY') . '&page=' . $request->input('page', 1);

        try {
            $response = $client->get($url);
            $moviesData = json_decode($response->getBody(), true);

            
            if (array_key_exists('results', $moviesData)) {
                $movies = $moviesData['results'];

                foreach ($movies as $movieData) {
                    try {
                        DB::table('movies')->insert([
                            'id' => $movieData['id'],
                            'title' => $movieData['title'],
                            'year' => substr($movieData['release_date'], 0, 4),
                            'overview' => $movieData['overview'],
                            
                        ]);
                    } catch (\Exception $ex) {
                        
                        error_log('Error inserting movie: ' . $ex->getMessage());
                    }
                }

                return response()->json(['message' => 'Peliculas subidas correctamente'], 200);
            } else {
                return response()->json(['error' => 'not found in the API response'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'title' => 'required|string|max:255',
        'year' => 'required|integer|digits:4',
        'overview' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 400);
    }

    try {
        $movie = Movie::find($id);

        if (!$movie) {
            return response()->json(['error' => 'Movie not found'], 404);
        }

        $movie->title = $request->input('title');
        $movie->year = $request->input('year');
        $movie->overview = $request->input('overview');
        // Agrega más campos según sea necesario

        $movie->save();

        return response()->json(['message' => 'Pelicula actualizada correctamente'], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
           
            $movie = Movie::find($id);
    
            
            if (!$movie) {
                return response()->json(['error' => 'Movie not found'], 404);
            }
    
            
            $movie->delete();
    
            return response()->json(['message' => 'Pelicula eliminada correctamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
