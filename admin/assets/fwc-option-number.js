window.onload = (event) =>{
	button = document.querySelector('.fwc-count-button');
	if ( button ) {
		button.addEventListener('click', (e) => {
			if ( confirm( fwcRestart.text ) ) {
				// AJAX request.
				fetch( fwcRestart.url, {
					method: 'POST',
					credentials: 'same-origin',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded',
						'Cache-Control': 'no-cache',
					},
					body: 'action=fwc_update_option_number&nonce=' + fwcRestart.nonce,
				})
				.then((response) => response.json())
				.then( response => {
					document.querySelector('.fwc-count-times')
					.innerHTML = response.data;
					document.querySelector('.fwc-information').innerHTML = '';
					button.remove();
				})
				.catch(err => console.log(err));	
			}
		});
	}
	filter = document.querySelector('.fwc-users .filter');
	if ( filter ) {
		filter.addEventListener('change', (e) => {
			// AJAX request.
			fetch( fwcFilter.url, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
					'Cache-Control': 'no-cache',
				},
				body: 'action=fwc_filter_users&nonce=' + fwcFilter.nonce + '&filter=' + filter.value,
			})
			.then((response) => response.json())
			.then( response => {
				document.querySelector('.fwc-information').innerHTML = response.data;
			})
			.catch(err => console.log(err));
		});
	}
};