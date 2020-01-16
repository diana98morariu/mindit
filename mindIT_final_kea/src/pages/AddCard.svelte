<script>
    import jq from "jquery";
	import toastr from "toastr";
    import { curRoute } from '../routing/router.js';

	const basicURL = '../../../backend/mindIT_backend/apis/';
    let cardData = {
        cardNumber: '',
        expDate: '',
        CVV: ''
    };

    let setAsPrimaryCard = false;

    toastr.options = {
		"positionClass": "toast-bottom-right",
		"preventDuplicates": true,
    }

    let somethingWasTouched = false, cardNumberWasTouched = false, expDateWasTouched = false, CVVWasTouched = false, triedWithEmpty = false;

    const areThereErrors = () => { return jq('.error').length > 0 ? true : false }

    const setFirstTouched = (input) => {
        somethingWasTouched = true;
		if(input === 'cardNumber') {cardNumberWasTouched = true};
		if(input === 'expDate') {expDateWasTouched = true};
		if(input === 'CVV') {CVVWasTouched = true};
    }
    
    const setPrimaryCard = () => {
        setAsPrimaryCard = setAsPrimaryCard ? false : true;
    }

	const validateInput = (elmValue, input) => {
		let isValid = false;
		triedWithEmpty = false;
		switch(input) {
			case 'cardNumber':
				isValid = (elmValue.replace(/ /g,'').length === 16 && /^\d+$/.test(elmValue.replace(/ /g,''))) ? true : false;
				break;
			case 'expDate':
				isValid = (elmValue.replace(/ /g,'').length === 7 && /^(0[1-9]|1[0-2])\/?([0-9]{4}|[0-9]{2})$/.test(elmValue.replace(/ /g,'')) && parseInt(elmValue.replace(/ /g,'').split('/')[1]) >= new Date().getFullYear());
				break;
			case 'CVV':
				isValid = (elmValue.replace(/ /g,'').length === 3 && /^\d+$/.test(elmValue.replace(/ /g,''))) ? true : false;
				break;
			default:
				// console.log(`VALIDATION FAILED: no validation for: ${input}`);
				break;
		}
		return isValid;
    }
    
    const addCard = (card_number, card_expDate, CVV) => {
         if(areThereErrors()) {
            toastr.error('Please make sure all the fields are completed and valid!');
            return;
        }
		if(validateInput(card_expDate, 'expDate') && validateInput(card_number, 'cardNumber') && validateInput(CVV, 'CVV') && somethingWasTouched) {
            const card_expMonth = card_expDate.split('/')[0];
            const card_expYear = card_expDate.split('/')[1];
            const cardIsPrimary = setAsPrimaryCard ? 1 : 0;
			jq.ajax({
				type: "POST",
				url: basicURL + "api-add-credit-card.php",
				dataType: "json",
				data: {
                    card_number: card_number,
                    card_expMonth: card_expMonth,
                    card_expYear: card_expYear,
                    card_CVV: CVV,
                    card_isPrimary: cardIsPrimary,
					token: localStorage.token
				},
				success: (data) => {
                    // console.log(data);
                    toastr.success('Your card has been added successfully');
                    cardData = {
                        cardNumber: '',
                        expDate: '',
                        CVV: ''
                    };
                    somethingWasTouched = false, cardNumberWasTouched = false, expDateWasTouched = false, CVVWasTouched = false, triedWithEmpty = false;
                    let setAsPrimaryCard = false;
					curRoute.set('/edit-cards');
					window.history.pushState({path: '/edit-cards'}, '', window.location.origin + '/edit-cards');
				},
				error: (err) => {
					// console.log(err);
				}
			});
		}
    }

</script>

<style>
    input{
        width: auto;
    }

    .row {
        border-radius: 10px;
        border: 1px solid rgba(128, 0, 128, 0.322);
    }

    .wrap_input_container{
        height: auto;
    }

    .add_card_button_container{
        padding: 1.4rem 0;
    }

    .set_primary_card_container{
        padding: 0.5rem 0;
        padding-bottom: 0.7rem;
    }

    .set_primary_card_container span{
        color: #E59967;
    }

    .purple_button{
        width: 100%;
        width: 24.5%;
    }

      /* LARGE TABLETS */
	@media (max-width: 1024px) {

	}

	/* TABLETS */
	@media (max-width: 768px) {
        .purple_button{
            width: 34.5%;
        }
	}

	/* MOBILE */
	@media (max-width: 414px) {
         .purple_button{
            width: 55.5%;
        }
	
	}
</style>

<div class="page_title">Add new credit card</div>

<div class="edit_cards_container">
    <div class='page_wrapper'>
        <div class='row'>
            <div class='column'>
                <div class="wrap_input_container">
                    <div class="inputElement">
                        <label for="text">
                            Card number
                        </label>
                        <input type="text" bind:value={cardData.cardNumber} placeholder="Card number" on:input={() => setFirstTouched('cardNumber')} />
                    </div>
                    {#if (!(validateInput(cardData.cardNumber, 'cardNumber')) && cardNumberWasTouched) || (!(validateInput(cardData.cardNumber, 'cardNumber')) && triedWithEmpty) }
                        <div class="error">Card number needs 16 numbers!</div>
                    {/if}
                </div>
            </div>
            <div class='column'>
                <div class="wrap_input_container">
                    <div class="inputElement">
                        <label for="text">
                            Expiration date
                        </label>
                        <input type="text" bind:value={cardData.expDate} placeholder="ex. 03/2019" on:input={() => setFirstTouched('expDate')} />
                    </div>
                    {#if (!(validateInput(cardData.expDate, 'expDate')) && expDateWasTouched) || (!(validateInput(cardData.expDate, 'expDate')) && triedWithEmpty) }
						<div class="error">MM/YYYY</div>
                    {/if}
                </div>
            </div>
            <div class='column'>
                <div class="wrap_input_container">
                    <div class="inputElement">
                        <label for="text">
                            CVV
                        </label>
                        <input type="text" bind:value={cardData.CVV} placeholder="ex. 865" on:input={() => setFirstTouched('CVV')} />
                    </div>
                    {#if (!(validateInput(cardData.CVV, 'CVV')) && CVVWasTouched) || (!(validateInput(cardData.CVV, 'CVV')) && triedWithEmpty) }
						<div class="error">CVV needs 3 numbers!</div>
                    {/if}
                </div>
            </div>
        </div>
    </div>
</div>
<div class="set_primary_card_container">
    <input type="checkbox" on:click={setPrimaryCard} bind:checked={setAsPrimaryCard}>
    <span on:click={setPrimaryCard}>Set as primary card</span>
</div>  
<div class="add_card_button_container">
    <button class="purple_button" on:click={() => {addCard(cardData.cardNumber, cardData.expDate, cardData.CVV)}}>Add new card</button>
</div>  
