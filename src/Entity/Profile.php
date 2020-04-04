<?php

namespace IDCT\Wykop\Entity;

use DateTime;
use IDCT\Wykop\Response\GenericResponse;
use InvalidArgumentException;

class Profile extends Author
{
    /**
     * User's login key (userkey).
     *
     * @var string
     */
    protected $key;

    /**
     * Signup date (if set)
     *
     * @var DateTime|null
     */
    protected $signupDate;

    /**
     * User's rank.
     *
     * @var int|null
     */
    protected $rank;

    /**
     * Profile background's url.
     *
     * @var string
     */
    protected $background;

    /**
     * Creates an instance of the Profile entity.
     * Fills attributes whenever possible with parameters provided in the response.
     *
     * @todo Cover more attributes
     * @param GenericResponse $response
     * @return $this
     * @throws InvalidArgumentException
     */
    public function __construct(GenericResponse $response)
    {
        $data = $response->getData();

        if (!isset($data['profile']) || !is_array($data['profile'])) {
            throw new InvalidArgumentException("Missing profile information.");
        }

        if (!isset($data['userkey']) || empty($data['userkey'])) {
            throw new InvalidArgumentException("Missing userkey.");
        }

        $profile = $data['profile'];

        if (!isset($profile['login']) || empty($profile['login'])) {
            throw new InvalidArgumentException("Missing login in the response.");
        }

        $this->color = isset($profile['color']) ? intval($profile['color']) : null;
        $this->avatar = isset($profile['avatar']) ? $profile['avatar'] : null;
        $this->sex = isset($profile['sex']) ? $profile['sex'] : null;
        $this->signupDate = isset($profile['signup_at']) ? new DateTime($profile['signup_at']) : null;
        $this->rank = isset($profile['rank']) ? intval($profile['rank']) : null;
        $this->background = isset($profile['background']) ? $profile['background'] : null;

        $this->login = $profile['login'];
        $this->key = $data['userkey'];
    }

    /**
     * Returns user's login key.
     *
     * @return string
     */
    public function getKey() : string
    {
        return $this->key;
    }

    /**
     * Returns user's rank.
     *
     * @return int|null
     */
    public function getRank() : ?int
    {
        return $this->rank;
    }

    /**
     * Returns user profile's background.
     *
     * @return string|null
     */
    public function getBackground() : ?string
    {
        return $this->background;
    }
}
