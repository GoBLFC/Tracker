<?php

namespace App\Models;

enum Role: int {
	case Admin = 3;
	case Manager = 2;
	case Lead = 1;
	case Volunteer = 0;
	case Banned = -1;
}
