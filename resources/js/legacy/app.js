import 'bootstrap/js/dist/modal';
import '../../sass/app.scss';

import { Toast } from './shared.js';

if (typeof flashSuccess !== 'undefined') {
	Toast.fire({
		text: flashSuccess,
		icon: 'success',
	});
} else if (typeof flashError !== 'undefined') {
	Toast.fire({
		text: flashError,
		icon: 'error',
	});
}
