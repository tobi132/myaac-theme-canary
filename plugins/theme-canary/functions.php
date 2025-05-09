<?php

function isVipSystemEnabled(): bool {
	return getBoolean(configLua('vipSystemEnabled'));
}
