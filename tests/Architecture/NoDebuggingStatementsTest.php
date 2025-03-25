<?php

arch()
    ->expect(['dd', 'ddd', 'die', 'dump', 'ray', 'sleep'])
    ->toBeUsedInNothing();
