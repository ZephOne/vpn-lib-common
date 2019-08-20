<?php

/*
 * eduVPN - End-user friendly VPN.
 *
 * Copyright: 2016-2019, The Commons Conservancy eduVPN Programme
 * SPDX-License-Identifier: AGPL-3.0+
 */

namespace LC\Common;

interface RandomInterface
{
    /**
     * @param int $length
     *
     * @return string
     */
    public function get($length);
}
