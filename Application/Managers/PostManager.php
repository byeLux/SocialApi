<?php

declare(strict_types=1);

namespace SocialApi\Application\Managers;

use OutOfBoundsException;
use SocialApi\Business\Services\PostService;
use SocialApi\Domain\Models\User;
use SocialApi\Domain\Interfaces\Repositories\IPostRepository;

class PostManager
{
    public function __construct(
        private PostService $postService,
        private IPostRepository $postRepository
    ) {
    }

    public function getPosts(int $page): array
    {
        $limit = $this->postRepository->getLimit();

        $this->postService->ensureValidPage($page, $limit);

        $posts = $this->postRepository->extractPosts($page);
        $totalPosts = $this->postRepository->getTotalPosts();
        $pagination = [
            'total_posts' => $totalPosts,
            'total_pages' => (int) ceil($totalPosts / $limit),
            'posts_per_page' => $limit,
            'current_page' => $page
        ];

        return [
            'pagination' => $pagination,
            'posts' => $posts
        ];
    }

    public function createPost(int $userId, string $content): void
    {
        $content = $this->postService->validateContent($content);

        $this->postRepository->create($userId, $content);
    }

    public function updatePost(User $user, int $postId, string $content): void
    {
        $this->postService->authorizeMutation($user);
        $content = $this->postService->validateContent($content);

        $post = $this->postRepository->findPostById($postId);

        if($post === null) {
            throw new OutOfBoundsException('post_not_found');
        }

        $this->postRepository->updatePost($postId, $content);

        if($this->postRepository->findPostById($postId) === null) {
            throw new OutOfBoundsException('post_not_found');
        }
    }

    public function deletePost(User $user, int $postId): void
    {
        $this->postService->authorizeMutation($user);

        $post = $this->postRepository->findPostById($postId);

        if($post === null || !$this->postRepository->delete($post)) {
            throw new OutOfBoundsException('post_not_found');
        }
    }
}
