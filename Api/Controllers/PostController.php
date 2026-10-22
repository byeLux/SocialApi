<?php

declare(strict_types=1);

namespace SocialApi\Api\Controllers;

use SocialApi\Api\Request;
use SocialApi\Api\JsonResponse;
use SocialApi\Application\Managers\PostManager;
use SocialApi\Domain\Interfaces\Serializers\IPostSerializer;

class PostController
{
    public function __construct(
        private PostManager $postManager,
        private IPostSerializer $postSerializer
    ) {
    }

    public function getPosts(Request $request): JsonResponse
    {
        $page = $request->getPage();

        $result = $this->postManager->getPosts($page);
        $postsToEncode = [];

        foreach($result['posts'] as $post) {
            $postsToEncode[] = $this->postSerializer->serialize($post);
        }

        return new JsonResponse([
            'pagination' => $result['pagination'],
            'posts' => $postsToEncode
        ]);
    }

    public function createPost(Request $request): JsonResponse
    {
        $body = $request->getJson();
        $content = $request->getString($body, 'content');

        $this->postManager->createPost($request->getUser()->getId(), $content);

        return new JsonResponse(['success' => true], 201);
    }

    public function updatePost(Request $request, string $id): JsonResponse
    {
        $postId = $request->getPositiveInteger($id);
        $body = $request->getJson();

        $content = $request->getString($body, 'content');

        $this->postManager->updatePost($request->getUser(), $postId, $content);

        return new JsonResponse(['success' => true]);
    }

    public function deletePost(Request $request, string $id): JsonResponse
    {
        $postId = $request->getPositiveInteger($id);
        $this->postManager->deletePost($request->getUser(), $postId);

        return new JsonResponse(['success' => true]);
    }
}
