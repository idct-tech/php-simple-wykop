<?php

namespace IDCT\Wykop\Entity;

use InvalidArgumentException;

class Author
{
    /**
     * User's username.
     *
     * @var string
     */
    protected $login;

    /**
     * User's colour (which varies depending on how long user is a member of wykop or the activity).
     *
     * @var int|null
     */
    protected $color;

    /**
     * Avatars's url (if set)
     *
     * @var string|null
     */
    protected $avatar;

    /**
     * User's sex.
     *
     * @var string
     */
    protected $sex;

    /**
     * Creates an instance of the Author entity.
     * Fills attributes whenever possible with parameters provided in the input array.
     *
     * @todo Enum for colors and sex
     * @param string[] $input
     * @return $this
     * @throws InvalidArgumentException
     */
    public function __construct(array $input)
    {
        if (!isset($input['login']) || empty($input['login'])) {
            throw new InvalidArgumentException("Missing login in the response.");
        }

        $this->color = isset($input['color']) ? intval($input['color']) : null;
        $this->avatar = isset($input['avatar']) ? $input['avatar'] : null;
        $this->sex = isset($input['sex']) ? $input['sex'] : null;

        $this->login = $input['login'];
    }

    /**
     * Returns user's login (username).
     *
     * @return string
     */
    public function getLogin() : string
    {
        return $this->login;
    }

    /**
     * Returns user's colour.
     *
     * @return int|null
     */
    public function getColor() : ?int
    {
        return $this->color;
    }

    /**
     * Returns user avatar's url.
     *
     * @return string|null
     */
    public function getAvatar() : ?string
    {
        return $this->avatar;
    }

    /**
     * Returns user's sex.
     *
     * @return string|null
     */
    public function getSex() : ?string
    {
        return $this->sex;
    }
}
