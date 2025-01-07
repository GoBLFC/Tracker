<?php

namespace App\Models;

enum AttendeeType: string {
	case Attendee = 'attendee';
	case Gatekeeper = 'gatekeeper';
}
