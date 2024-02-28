<?php

namespace App\Entity\Post;

use App\Entity\Trait\CategoryTagTrait;
use App\Repository\Post\TagRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ORM\HasLifecycleCallbacks()]
#[UniqueEntity('slug', 'Ce slug existe déjà')]
class Tag
{
    use CategoryTagTrait;
    #[ORM\ManyToMany(targetEntity: Post::class, inversedBy: 'tags')]
    #[ORM\JoinTable(name: 'tags_posts')]
    private Collection $posts;

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
        }

        return $this;
    }

    public function removePost(Post $post): static
    {
        $this->posts->removeElement($post);

        return $this;
    }
}
