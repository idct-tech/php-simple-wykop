<?php

namespace IDCT\Wykop\Response;

use InvalidArgumentException;

/**
 * Generic Response class.
 *
 * Should be used only if there is no dedicated handler for Wykop API's functionality.
 */
class GenericResponse
{
    /**
     * Array of data returned by Wykop's API.
     *
     * @var array
     */
    protected $data;

    /**
     * Url of the next page of the data.
     *
     * @var string
     */
    protected $paginationNext;

    /**
     * Url of the prev page of the data.
     *
     * @var string
     */
    protected $paginationPrev;

    /**
     * Creates a new instance of generic response entity.
     *
     * @param array $data Raw response of data returned by Wykop's API.
     */
    public function __construct(array $response, string $paginationNext = null, string $paginationPrev = null)
    {
        if (!isset($response['data'])) {
            throw new InvalidArgumentException("Response array does not contain `data`.");
        }

        $this->data = $response['data'];

        if (isset($response['pagination'])) {
            $this->paginationNex = isset($response['pagination']['next']) && !empty($response['pagination']['next']) ? $response['pagination']['next'] : null;
            $this->paginationPrev = isset($response['pagination']['prev']) && !empty($response['pagination']['prev']) ? $response['pagination']['prev'] : null;
        }
    }

    /**
     * Returns the raw array of data returned by Wykop's API.
     *
     * @return array
     */
    public function getData() : array
    {
        return $this->data;
    }
    
    /**
     * Returns url of the next page of results or null if next page was not present.
     *
     * @return string|null
     */
    public function getPaginationNext() : ?string
    {
        return $this->paginationNext;
    }

    /**
     * Returns url of the prev page of results or null if next page was not present.
     *
     * @return string|null
     */
    public function getPaginationPrev() : ?string
    {
        return $this->paginationPrev;
    }
}
