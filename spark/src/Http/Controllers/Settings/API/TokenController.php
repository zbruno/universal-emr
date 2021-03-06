<?php

namespace Laravel\Spark\Http\Controllers\Settings\API;

use Laravel\Spark\Spark;
use Illuminate\Http\Request;
use Laravel\Spark\Http\Controllers\Controller;
use Laravel\Spark\Contracts\Repositories\TokenRepository;
use Laravel\Spark\Http\Requests\Settings\API\CreateTokenRequest;
use Laravel\Spark\Http\Requests\Settings\API\UpdateTokenRequest;

class TokenController extends Controller
{
    /**
     * The token repository instance.
     *
     * @var TokenRepository
     */
    protected $tokens;

    /**
     * Create a new controller instance.
     *
     * @param  TokenRepository  $tokens
     * @return void
     */
    public function __construct(TokenRepository $tokens)
    {
        $this->tokens = $tokens;

        $this->middleware('auth');
    }

    /**
     * Get all of the tokens generated by the user.
     *
     * @param  Request  $request
     * @return Response
     */
    public function all(Request $request)
    {
        return $request->user()->tokens()
                    ->where('transient', false)
                    ->orderBy('created_at', 'desc')
                    ->get();
    }

    /**
     * Create a new API token for the user.
     *
     * @param  CreateTokenRequest  $request
     * @return Response
     */
    public function store(CreateTokenRequest $request)
    {
        $data = count(Spark::tokensCan()) > 0 ? ['abilities' => $request->abilities] : [];

        return response()->json(['token' => $this->tokens->createToken(
            $request->user(), $request->name, $data
        )->token]);
    }

    /**
     * Update the given API token.
     *
     * @param  UpdateTokenRequest  $request
     * @param  string  $tokenId
     * @return Response
     */
    public function update(UpdateTokenRequest $request, $tokenId)
    {
        $token = $request->user()->tokens()->where('id', $tokenId)->firstOrFail();

        $this->tokens->updateToken(
            $token, $request->name, (array) $request->abilities
        );
    }

    /**
     * Delete the given token.
     *
     * @param  Request  $request
     * @param  string  $tokenId
     * @return Response
     */
    public function destroy(Request $request, $tokenId)
    {
        $request->user()->tokens()->where('id', $tokenId)->firstOrFail()->delete();
    }
}
