<?php

namespace IDCT\Wykop\Entity;

use DateTime;
use InvalidArgumentException;

class Notification
{
    /**
     * Numerical identifier of the notification.
     *
     * @var int
     */
    protected $id;

    /**
     * Instance of Author with information about the person who triggered the notification.
     *
     * Optional.
     *
     * @var Author
     */
    protected $author;

    /**
     * Date and time of notification's creation (in Wykop).
     *
     * @var DateTime|null
     */
    protected $date;

    /**
     * Array of body contents: grouped by format (html, text).
     *
     * @var string[string]
     */
    protected array $body;

    /**
     * Type of the notification.
     *
     * @todo enum
     * @var string
     */
    protected string $type;

    /**
     * Item's id.
     *
     * @todo provide better description
     * @var int|null
     */
    protected int $itemId;

    /**
     * Subitem's id.
     *
     * @todo provide better description
     * @var int|null
     */
    protected $subitemId;

    /**
     * The url to view the notification.
     *
     * Optional.
     *
     * @var string
     */
    protected string $url;

    /**
     * Informs if the notification is new (if user has marked it as read).
     *
     * Optional.
     *
     * @var bool
     */
    protected bool $isNew;

    public function __construct(array $input)
    {
        if (!isset($input['id']) || !is_int($input['id'])) {
            throw new InvalidArgumentException("Notification must have an id.");
        }

        $this->author = isset($input['author']) && is_array($input['author']) ? new Author($input['author'])  : null;
        $this->date = isset($input['date']) && is_string($input['date']) ? new DateTime($input['date']) : null;
        $this->body = isset($input['body']) && is_array($input['body']) ? $input['body'] : null;
        $this->type = isset($input['type']) ? $input['type'] : null;
        $this->itemId = isset($input['item_id']) ? intval($input['item_id']) : null;
        $this->id = isset($input['id']) ? intval($input['id']) : null;
        $this->subitemId = isset($input['subitem_id']) ? intval($input['subitem_id']) : null;
        $this->url = isset($input['url']) ? $input['url'] : null;
        $this->isNew = isset($input['new']) ? boolval($input['new']) : null;
    }

    public function getId() : int
    {
        return $this->id;
    }

    /**
     * Returns an instance of Author with information about the person who triggered the notification.
     *
     * Optional.
     *
     * @return Author|null
     */
    public function getAuthor() : ?Author
    {
        return $this->author;
    }

    /**
     * Returns the date and time of notification's creation (in Wykop).
     *
     * @var DateTime|null
     */
    public function getDate() : ?DateTime
    {
        return $this->date;
    }

    /**
     * Gets the whole array of body contents identified by type if $type not provided.
     * If $type is provided then attempts to return body content identified by that value or null if not present.
     *
     * @return string|null|array
     */
    public function getBody(string $type = null)
    {
        if ($type === null) {
            return $this->body;
        }

        return isset($this->body[$type]) ? $this->body[$type] : null;
    }

    /**
     * Returns the notification's type.
     * @return string|null
     */
    public function getType() : ?string
    {
        return $this->type;
    }

    /**
     * Returns the item's id.
     *
     * @return int|null
     */
    public function getItemId() : ?int
    {
        return $this->itemId;
    }

    /**
     * Returns the subitem's id.
     *
     * Optional.
     *
     * @return int|null
     */
    public function getSubitemId() : ?int
    {
        return $this->subitemId;
    }

    /**
     * Returns the url to view the notification.
     *
     * Optional.
     *
     * @var string
     */
    public function getUrl() : ?string
    {
        return $this->url;
    }

    /**
     * Informs if the notification is new (if user has marked it as read).
     *
     * Optional.
     *
     * @return bool|null
     */
    public function isNew()
    {
        return $this->isNew;
    }
}
