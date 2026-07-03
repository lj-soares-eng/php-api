<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\DuplicateEmailException;
use App\Exceptions\UserNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class UserController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    public function index(): JsonResponse
    {
        $users = $this->userService->list();

        return UserResource::collection($users)->response();
    }

    public function show(int $id): JsonResponse
    {
        try {
            $user = $this->userService->find($id);
        } catch (UserNotFoundException $exception) {
            return response()->json(['detail' => $exception->getMessage()], Response::HTTP_NOT_FOUND);
        }

        return (new UserResource($user))->response();
    }

    public function store(CreateUserRequest $request): JsonResponse
    {
        try {
            $user = $this->userService->create($request->validated());
        } catch (DuplicateEmailException $exception) {
            return response()->json(['detail' => $exception->getMessage()], Response::HTTP_CONFLICT);
        }

        return (new UserResource($user))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        try {
            $user = $this->userService->update($id, $request->validated());
        } catch (UserNotFoundException $exception) {
            return response()->json(['detail' => $exception->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (DuplicateEmailException $exception) {
            return response()->json(['detail' => $exception->getMessage()], Response::HTTP_CONFLICT);
        }

        return (new UserResource($user))->response();
    }

    public function destroy(int $id): Response|JsonResponse
    {
        try {
            $this->userService->delete($id);
        } catch (UserNotFoundException $exception) {
            return response()->json(['detail' => $exception->getMessage()], Response::HTTP_NOT_FOUND);
        }

        return response()->noContent();
    }
}
