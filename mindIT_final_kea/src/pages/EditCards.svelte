<script>
    import jq from "jquery";
	import toastr from "toastr";
    import { curRoute } from '../routing/router.js';

	const basicURL = '../../../backend/mindIT_backend/apis/';
    let cardsData = {};

    toastr.options = {
		"positionClass": "toast-bottom-right",
		"preventDuplicates": false,
    }

    let somethingWasTouched = false, cardNumberWasTouched = false, expDateWasTouched = false, CVVWasTouched = false, triedWithEmpty = false;

	const getUserCards = async () => {
		const cards = await jq.ajax({
            type: 'GET',
			url: basicURL + 'api-get-user-cards.php',
			dataType: "json",
			data: {
				token: localStorage.token
			},
			success: (data) => {
                cardsData = data;
                cardsData.forEach(card => {
                    card.number = card.number.replace(/(\d{4})/g, '$1 ').replace(/(^\s+|\s+$)/,'');
                    card.number = card.number.replace(/^.{9}/g, '**** ****');      
                });
                // console.log(cardsData);
                // return cardsData;
			},
			error: error => {
				// console.log(error);
			}
		});
		if (cards) {
			return cards;
		} else {
            throw new Error();
		}
    }
    
    let promiseCards = getUserCards();

    const areThereErrors = () => { return jq('.error').length > 0 ? true : false }

    const setFirstTouched = (input) => {
        somethingWasTouched = true;
		if(input === 'cardNumber') {cardNumberWasTouched = true};
		if(input === 'expDate') {expDateWasTouched = true};
		if(input === 'CVV') {CVVWasTouched = true};
	}

	const validateInput = (elmValue, input) => {
		let isValid = false;
		triedWithEmpty = false;
		switch(input) {
			case 'cardNumber':
				isValid = (elmValue.replace(/ /g,'').length === 16 && /^\d+$/.test(elmValue.replace(/ /g,''))) ? true : false;
				break;
			case 'expDate':
				isValid = (elmValue.replace(/ /g,'').length === 7 && /^(0[1-9]|1[0-2])\/?([0-9]{4}|[0-9]{2})$/.test(elmValue.replace(/ /g,'')) );
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
    
    const toEditCard = (card_id, card_expDate, CVV) => {
        if(areThereErrors()) {
            toastr.error('Please make sure all the fields are completed and valid!');
            return;
        }
        if(!(parseInt(card_expDate.replace(/ /g,'').split('/')[1]) >= new Date().getFullYear())){
            toastr.error('Your card is expired! Please use a valid card');
            return;
        }
		if(validateInput(card_expDate, 'expDate') && validateInput(CVV, 'CVV') && somethingWasTouched) {
            const card_expMonth = card_expDate.split('/')[0];
            const card_expYear = card_expDate.split('/')[1];
			jq.ajax({
				type: "POST",
				url: basicURL + "api-edit-credit-card.php",
				dataType: "json",
				data: {
                    card_id: card_id,
                    card_expMonth: card_expMonth,
                    card_expYear: card_expYear,
                    card_CVV: CVV,
					token: localStorage.token
				},
				success: (data) => {
                    // console.log(data);
					toastr.success('Your card has been edited successfully');
				},
				error: (err) => {
					// console.log(err);
				}
			});
		} else if(!somethingWasTouched) {
            toastr.info('No changes were made! Please make a change in order to edit the card');
        }
    }

    const toAddCreditCardPage = () => {
        curRoute.set('/add-card');
		window.history.pushState({path: '/add-card'}, '', window.location.origin + '/add-card');
    }

    const changePrimaryCard = (card_id) => {
        jq.ajax({
            type: "GET",
            url: basicURL + "api-set-primary-card.php",
            dataType: "json",
            data: {
                cardID: card_id,
                token: localStorage.token
            },
            success: (data) => {
                cardsData.forEach((card) => { card.isPrimary = 0 });
                const filteredData = cardsData.filter((card) => {return card.id === card_id});
                filteredData[0].isPrimary = 1;
                cardsData.sort((a, b) => parseFloat(b.isPrimary) - parseFloat(a.isPrimary));
                promiseCards = cardsData;
                toastr.success('Primary card updated successfully');
            },
            error: (error) => {
                // console.log(error);
            }
        });
    }

    const deleteCard = (card_id) => {
        jq.ajax({
            type: "GET",
            url: basicURL + "api-delete-credit-card.php",
            dataType: "json",
            data: {
                cardID: card_id,
                token: localStorage.token
            },
            success: (data) => {
                toastr.success('Card deleted successfully');
                cardsData = cardsData.filter((card) => {return card.id !== card_id});
                promiseCards = cardsData;
            },
            error: (error) => {
                // console.log(error);
            }
        });
    }

</script>

<style>
    .primary_card{
        color: green;
        margin-top: .35rem;
        padding-left: .3rem;
        font-size: 13px;
        margin-bottom: 1.4rem;
    }

    input{
        width: auto;
        width: 90%;
    }

    .primary{
        background: rgba(13, 119, 13, 0.137);
        border: none;
    }

    .row {
        border-radius: 10px;
        border: 1px solid rgba(128, 0, 128, 0.322);
        margin-bottom: 1rem;
    }

    .purple_button, .orange_button {
        width: 90%;
    }
    .wrap_input_container{
        height: auto;
    }

    .add_card_button_container{
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
    }

    #add_card_button{
        font-size: 17px;
        margin-left: .1rem;
        cursor: pointer;
        background: transparent;
        border: none; 
        color: rgba(128, 0, 128, 0.5);
        text-decoration: underline;
        margin: 1.5rem 0;
    }

    .set_card_primary_button{
        background: transparent;
        color: #E59967;
    }

    .set_card_primary_button:hover{
        background: #9E5BD8;
        color: white;
    }

    /* LARGE TABLETS */
	@media (max-width: 1024px) {

	}

	/* TABLETS */
	@media (max-width: 768px) {
        .set_card_primary_button{
            padding: 0;
            font-size: 14px;
        }
	}

	/* MOBILE */
	@media (max-width: 414px) {

	}

</style>

{#await promiseCards}
    <div class="loader">Loading...</div>
{:then cards}
    <div class="page_title">Edit cards</div>

    <div class="edit_cards_container">
        {#each cards as card}
        <div class='page_wrapper'>
		    <div class='row' class:primary="{card.isPrimary === 1}">
			    <div class='column'>
                    <div class="wrap_input_container">
                        <div class="inputElement">
                            <label for="text">
                                Card number
                            </label>
                            <input class="readonly_input card_number" type="text" bind:value={card.number} readonly />
                        </div>
                    </div>
                    {#if card.isPrimary !== 1}
                        <div class="wrap_input_container">
                            <div class="wrap_buttons_edit_cards">
                                <button class="orange_button set_card_primary_button" on:click={() => changePrimaryCard(card.id)}>Set as primary card</button>
                            </div>
                        </div>
                    {/if}
                </div>
                <div class='column'>
                    <div class="wrap_input_container">
                        <div class="inputElement">
                            <label for="text">
                                Expiration date
                            </label>
                            <input type="text" bind:value={card.expDate} placeholder="ex. 03/2019" on:input={() => setFirstTouched('expDate')} />
                        </div>
                        {#if (!(validateInput(card.expDate, 'expDate')) && expDateWasTouched) || (!(validateInput(card.expDate, 'expDate')) && triedWithEmpty) }
						<div class="error">MM/YYYY</div>
                        {/if}
                    </div>
                    <div class="wrap_input_container">
                        <div class="wrap_buttons_edit_cards">
                            <button class="purple_button edit_card_button" on:click={() => toEditCard(card.id, card.expDate, card.CVV)}>Edit card</button>
                        </div>
				    </div>
                </div>
                <div class='column'>
                    <div class="wrap_input_container">
                        <div class="inputElement">
                            <label for="text">
                                CVV
                            </label>
                            <input type="text" bind:value={card.CVV} placeholder="ex. 865" on:input={() => setFirstTouched('CVV')} />
                        </div>
                        {#if (!(validateInput(card.CVV, 'CVV')) && CVVWasTouched) || (!(validateInput(card.CVV, 'CVV')) && triedWithEmpty) }
						<div class="error">CVV needs 3 numbers!</div>
                        {/if}
                    </div>
                    {#if card.isPrimary !== 1}
                        <div class="wrap_input_container">
                            <div class="wrap_buttons_edit_cards">
                                <button class="orange_button" on:click={() => deleteCard(card.id)}>Delete card</button>
                            </div>
                        </div>
                    {/if}
                </div>
		    </div>
        </div>
        {#if card.isPrimary === 1}
            <div class="primary_card">This card is your primary card. Only payments from this card will be made!</div>
        {/if}
        {/each}
    </div>
    <div class="add_card_button_container">
        <button id="add_card_button" on:click={toAddCreditCardPage}>Add new card</button>
    </div>  
{:catch error}
	<p style="color: red">{error.message}</p>
{/await}