<?php

namespace nadir2\core;

/**
 * This is composite class of the View (the view in a broad sense), which
 * entities may contains the atomic view units - the snippets.
 * @author Leonid Selikhov
 */
abstract class AbstractCompositeView extends AbstractView
{
    /** @var \nadir2\core\Snippet[] The snippet map. */
    protected $snippets = [];

    /**
     * It adds snippet to the view object.
     * @param string $snptName
     * @return void
     */
    public function addSnippet(string $snptName): void
    {
        $this->snippets[$snptName] = ViewFactory::createSnippet($snptName);
    }

    /**
     * It returns assigned snippet object by the name. If the name not presents,
     * then it returns the map with all View-assigned snippets.
     * @param string $snptName
     * @return \nadir2\core\Snippet|\nadir2\core\Snippet[]|null
     */
    public function getSnippet(string $snptName = '')
    {
        if (empty($snptName)) {
            return $this->snippets;
        } else {
            return isset($this->snippets[$snptName]) ? $this->snippets[$snptName]
                    : null;
        }
    }

    /**
     * The method returns the View-assigned snippet map.
     * @return \nadir2\core\Snippet[]
     */
    public function getAllSnippets(): array
    {
        return $this->getSnippet();
    }
}
