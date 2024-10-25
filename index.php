<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Shop Pay Integration</title>
        <script src="https://cdn.shopify.com/shopifycloud/shop-js/shop-pay-payment-request.js"></script>
        <style>
            shop-pay-payment-request-button {
                --shop-pay-button-width: 200px;
                --shop-pay-button-border-radius: 4px;
                
            }
            #email-input {
                width: 178px;
                padding: 10px;
                border: 1px solid #ccc;
                border-radius: 4px;
            }
            #thank-you-message {
                display: none;
                margin-top: 20px;
                font-weight: bold;
            }
            #reload-button {
                display: none;
                margin-top: 10px;
                padding: 10px 15px;
                background-color: #007bff;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
            }
            #reload-button:hover {
                background-color: #0056b3;
            }
        </style>
    </head>  
<body>
    <div id="shop-pay-button-container"></div>
    <div id="shop-pay-login-container">
        <input type="email" id="email-input" name="email"/>
    </div>
    <script>
        // Configure Shop Pay Payment Request
        window.ShopPay.PaymentRequest.configure({
            shopId: 59678883862,
            clientId: "newplustest.myshopify.com",
            debug: true
        });

        // Create and render the Shop Pay button and login
        window.ShopPay.PaymentRequest
            .createButton().render('#shop-pay-button-container');
        window.ShopPay.PaymentRequest
            .createLogin({emailInputId: 'email-input'}).render('#shop-pay-login-container');

        // Initial payment request setup
        const initialPaymentRequest = window.ShopPay.PaymentRequest.build({
            lineItems: [
                {
                    label: "T-Shirt",
                    originalItemPrice: {
                        amount: 10.00,
                        currencyCode: "USD"
                    },
                    itemDiscounts: [],
                    finalItemPrice: {
                        amount: 10.00,
                        currencyCode: "USD"
                    },
                    quantity: 2,
                    sku: "t-shirt",
                    requiresShipping: true,
                    originalLinePrice: {
                        amount: 20.00,
                        currencyCode: "USD"
                    },
                    lineDiscounts: [],
                    finalLinePrice: {
                        amount: 20.00,
                        currencyCode: "USD"
                    },
                }
            ],
            discountCodes: [],
            deliveryMethods: [],
            subtotal: {
                amount: 20.00,
                currencyCode: "USD"
            },
            totalTax: {
                amount: 2.50,
                currencyCode: "USD"
            },
            total: {
                amount: 22.50,
                currencyCode: "USD"
            },
            presentmentCurrency: "USD",
            locale: 'en',
            totalShippingPrice: {
                finalTotal: {
                    amount: 0,
                    currencyCode: "USD",
                },
            }
        });

        const session = window.ShopPay.PaymentRequest.createSession({
            paymentRequest: initialPaymentRequest
        });

        // Track original shipping and pickup rates
        const originalRates = {
            STANDARD_US: 5.00,
            EXPRESS_US: 15.00,
            STANDARD_CA: 5.00,
            EXPRESS_CA: 15.00,
            PICKUP_US: 8.00, // Assuming pickup is free
            PICKUP_CA: 8.00  // Assuming pickup is free
        };

        // Set initial delivery method type to "SHIPPING"
        let currentDeliveryMethodType = "SHIPPING";

        // Event listeners
        session.addEventListener("sessionrequested", async (ev) => {
            const response = await fetch('/server.php', {
                method: 'POST',
                body: JSON.stringify({
                    action: 'createSession',
                    payment_request: initialPaymentRequest
                }),
                headers: {
                    'Content-Type': 'application/json',
                },
            });
            const data = await response.json();
            const {token, checkoutUrl, sourceIdentifier} = data;
            session.completeSessionRequest({token, checkoutUrl, sourceIdentifier});
        });

        session.addEventListener("shippingaddresschanged", async (ev) => {
            const currentPaymentRequest = session.paymentRequest;
            const selectedAddress = ev.shippingAddress;
            let deliveryMethods = [];
            let pickupLocations = [];

            if (selectedAddress.countryCode === "US") {
                deliveryMethods = [
                    {
                        label: "Standard (US)",
                        amount: {
                            amount: 5.00,
                            currencyCode: "USD"
                        },
                        code: "STANDARD_US",
                        minDeliveryDate: '2024-01-01',
                        maxDeliveryDate: '2026-01-01',
                    },
                    {
                        label: "Express (US)",
                        amount: {
                            amount: 15.00,
                            currencyCode: "USD"
                        },
                        code: "EXPRESS_US",
                        minDeliveryDate: '2024-01-01',
                        maxDeliveryDate: '2025-01-01',
                    }
                ];

                pickupLocations = [
                    {
                        label: "Location A (US)",
                        code: "PICKUP_US",
                        detail: "123 Main St, City, US",
                        amount: {
                            amount: 8.00,
                            currencyCode: "USD"
                        },
                        readyExpectationLabel: "Ready in 1 hour",
                        proximityLabel: "Near Main St",
                    },
                    {
                        label: "Location B (US)",
                        code: "PICKUP_US",
                        detail: "456 Elm St, City, US",
                        amount: {
                            amount: 8.00,
                            currencyCode: "USD"
                        },
                        readyExpectationLabel: "Ready in 1 hour",
                        proximityLabel: "Near Elm St",
                    }
                ];
            } else if (selectedAddress.countryCode === "CA") {
                deliveryMethods = [
                    {
                        label: "Standard (Canada)",
                        amount: {
                            amount: 5.00,
                            currencyCode: "USD"
                        },
                        code: "STANDARD_CA",
                        minDeliveryDate: '2024-01-01',
                        maxDeliveryDate: '2026-01-01',
                        deliveryExpectationLabel: "3-5 business days",
                        detail: "test",
                        
                    },
                    {
                        label: "Express (Canada)",
                        amount: {
                            amount: 15.00,
                            currencyCode: "USD"
                        },
                        code: "EXPRESS_CA",
                        minDeliveryDate: '2024-01-01',
                        maxDeliveryDate: '2025-01-12',
                        deliveryExpectationLabel: "1-2 business days",
                    }
                ];

                pickupLocations = [
                    {
                        label: "Location A (Canada)",
                        code: "PICKUP_CA",
                        detail: "123 Main St, City, Canada",
                        amount: {
                            amount: 8.00,
                            currencyCode: "USD"
                        },
                        readyExpectationLabel: "Ready in 1 hour",
                        proximityLabel: "Near Main St",
                    },
                    {
                        label: "Location B (Canada)",
                        code: "PICKUP_CA",
                        detail: "456 Elm St, City, Canada",
                        amount: {
                            amount: 8.00,
                            currencyCode: "USD"
                        },
                        readyExpectationLabel: "Ready in 1 hour",
                        proximityLabel: "Near Elm St",
                    }
                ];
            }

            const updatedPaymentRequest = window.ShopPay.PaymentRequest.build({
                ...currentPaymentRequest,
                deliveryMethods: deliveryMethods,
                supportedDeliveryMethodTypes: ["SHIPPING", "PICKUP"],
                pickupLocations: pickupLocations
            });
            session.completeShippingAddressChange({ updatedPaymentRequest: updatedPaymentRequest });
        });

        session.addEventListener("deliverymethodtypechanged", async (ev) => {
            const currentPaymentRequest = session.paymentRequest;
            const deliveryMethodType = ev.deliveryMethodType;
            let pickupLocations = [];
            let updatedDiscountCodes = [...currentPaymentRequest.discountCodes];
            let errors = [];

            // Update the current delivery method type
            currentDeliveryMethodType = deliveryMethodType;

            // Toggle discount codes based on delivery method type
            if (deliveryMethodType === 'PICKUP') {
                if (updatedDiscountCodes.includes("shipping")) {
                    updatedDiscountCodes = updatedDiscountCodes.filter(code => code.toLowerCase() !== "shipping");
                    updatedDiscountCodes.push("pickup");
                    errors.push({
                        type: "generalError",
                        message: "Free Shipping discount is not applicable for pickup orders. Converting discount to free pickup!"
                    });
                }
            } else if (deliveryMethodType === 'SHIPPING') {
                if (updatedDiscountCodes.includes("pickup")) {
                    updatedDiscountCodes = updatedDiscountCodes.filter(code => code.toLowerCase() !== "pickup");
                    updatedDiscountCodes.push("shipping");
                    errors.push({
                        type: "generalError",
                        message: "Free Pickup discount is not applicable for shipping orders. Converting discount to free shipping!"
                    });
                }
            }

            if (deliveryMethodType === 'PICKUP') {
                const selectedAddress = currentPaymentRequest.shippingAddress;
                if (selectedAddress.countryCode === "US") {
                    pickupLocations = [
                        {
                            label: "Location A (US)",
                            code: "PICKUP_US",
                            detail: "123 Main St, City, US",
                            amount: {
                                amount: 8.00,
                                currencyCode: "USD"
                            },
                            readyExpectationLabel: "Ready in 1 hour",
                            proximityLabel: "Near Main St",
                        },
                        {
                            label: "Location B (US)",
                            code: "PICKUP_US",
                            detail: "456 Elm St, City, US",
                            amount: {
                                amount: 8.00,
                                currencyCode: "USD"
                            },
                            readyExpectationLabel: "Ready in 1 hour",
                            proximityLabel: "Near Elm St",
                        }
                    ];
                } else if (selectedAddress.countryCode === "CA") {
                    pickupLocations = [
                        {
                            label: "Location A (Canada)",
                            code: "PICKUP_CA",
                            detail: "123 Main St, City, Canada",
                            amount: {
                                amount: 8.00,
                                currencyCode: "USD"
                            },
                            readyExpectationLabel: "Ready in 1 hour",
                            proximityLabel: "Near Main St",
                        },
                        {
                            label: "Location B (Canada)",
                            code: "PICKUP_CA",
                            detail: "456 Elm St, City, Canada",
                            amount: {
                                amount: 8.00,
                                currencyCode: "USD"
                            },
                            readyExpectationLabel: "Ready in 1 hour",
                            proximityLabel: "Near Elm St",
                        }
                    ];
                }
            }

            // Update the discount array based on the new delivery method type
            let discounts = [];
            let totalDiscountAmount = 0;
            updatedDiscountCodes.forEach(code => {
                if (code === "shipping" && deliveryMethodType === "SHIPPING") {
                    const shippingDiscountAmount = originalRates[currentPaymentRequest.shippingLines[0].code];
                    totalDiscountAmount += shippingDiscountAmount;
                    discounts.push({
                        label: "Free Shipping",
                        amount: {
                            amount: shippingDiscountAmount,
                            currencyCode: "USD"
                        }
                    });
                } else if (code === "pickup" && deliveryMethodType === "PICKUP") {
                    const pickupDiscountAmount = pickupLocations[0].amount.amount; // Use the first pickup location amount
                    totalDiscountAmount += pickupDiscountAmount;
                    discounts.push({
                        label: "Free Pickup",
                        amount: {
                            amount: pickupDiscountAmount,
                            currencyCode: "USD"
                        }
                    });
                }
            });

            // Recalculate the total amount
            const newTotal = currentPaymentRequest.subtotal.amount + currentPaymentRequest.totalTax.amount + currentPaymentRequest.totalShippingPrice.finalTotal.amount - totalDiscountAmount;

            const updatedPaymentRequest = window.ShopPay.PaymentRequest.build({
                ...currentPaymentRequest,
                pickupLocations,
                supportedDeliveryMethodTypes: ["SHIPPING", "PICKUP"],
                discountCodes: updatedDiscountCodes,
                discounts: discounts,
                total: {
                    amount: newTotal,
                    currencyCode: "USD"
                },
                shippingLines: deliveryMethodType === 'PICKUP' ? [] : currentPaymentRequest.shippingLines,
                totalShippingPrice: deliveryMethodType === 'PICKUP' ? {
                    finalTotal: {
                        amount: pickupLocations[0].amount.amount, // Use the first pickup location amount
                        currencyCode: "USD"
                    }
                } : {
                    finalTotal: {
                        amount: originalRates[currentPaymentRequest.shippingLines[0].code],
                        currencyCode: "USD"
                    }
                }
            });

            session.completeDeliveryMethodTypeChange({ updatedPaymentRequest: updatedPaymentRequest, errors: errors });
        });

        session.addEventListener("deliverymethodchanged", async (ev) => {
            const currentPaymentRequest = session.paymentRequest;
            const selectedDeliveryMethod = ev.deliveryMethod;
            let updatedPaymentRequest;

            const shippingDiscount = (currentPaymentRequest.discounts || []).find(discount => discount.label === "Free Shipping");
            const shippingAmount = shippingDiscount ? 0 : selectedDeliveryMethod.amount.amount;

            updatedPaymentRequest = window.ShopPay.PaymentRequest.build({
                ...currentPaymentRequest,
                shippingLines: [{
                    label: selectedDeliveryMethod.label,
                    amount: {
                        amount: shippingAmount,
                        currencyCode: selectedDeliveryMethod.amount.currencyCode
                    },
                    code: selectedDeliveryMethod.code
                }],
                totalShippingPrice: {
                    finalTotal: {
                        amount: selectedDeliveryMethod.amount.amount,
                        currencyCode: selectedDeliveryMethod.amount.currencyCode,
                    },
                },
                total: {
                    amount: currentPaymentRequest.subtotal.amount + currentPaymentRequest.totalTax.amount + shippingAmount - (currentPaymentRequest.discounts || []).reduce((acc, discount) => acc + discount.amount.amount, 0),
                    currencyCode: "USD"
                },
                supportedDeliveryMethodTypes: ["SHIPPING", "PICKUP"]
            });

            session.completeDeliveryMethodChange({ updatedPaymentRequest: updatedPaymentRequest });
        });

        session.addEventListener("pickuplocationchanged", async (ev) => {
            const currentPaymentRequest = session.paymentRequest;
            const pickupLocation = ev.pickupLocation;
            const updatedPaymentRequest = window.ShopPay.PaymentRequest.build({
                ...currentPaymentRequest,
                totalShippingPrice: {
                    finalTotal: {
                        amount: pickupLocation.amount.amount,
                        currencyCode: "USD",
                    },
                },
                total: {
                    amount: currentPaymentRequest.subtotal.amount + currentPaymentRequest.totalTax.amount + pickupLocation.amount.amount - (currentPaymentRequest.discounts || []).reduce((acc, discount) => acc + discount.amount.amount, 0),
                    currencyCode: "USD"
                },
                shippingLines: [{
                    label: pickupLocation.label,
                    amount: {
                        amount: pickupLocation.amount.amount,
                        currencyCode: pickupLocation.amount.currencyCode
                    },
                    code: pickupLocation.code
                }],
                supportedDeliveryMethodTypes: ["SHIPPING", "PICKUP"]
            });
            session.completePickupLocationChange({ updatedPaymentRequest: updatedPaymentRequest });
        });

        session.addEventListener("pickuplocationfilterchanged", async (ev) => {
            const currentPaymentRequest = session.paymentRequest;
            const buyerLocation = ev.buyerLocation;
            let pickupLocations = [];

            if (buyerLocation.countryCode === "US") {
                pickupLocations = [
                    {
                        label: "Location A (US)",
                        code: "PICKUP_US",
                        detail: "123 Main St, City, US",
                        amount: {
                            amount: 8.00,
                            currencyCode: "USD"
                        },
                        readyExpectationLabel: "Ready in 1 hour",
                        proximityLabel: "Near Main St",
                    },
                    {
                        label: "Location B (US)",
                        code: "PICKUP_US",
                        detail: "456 Elm St, City, US",
                        amount: {
                            amount: 8.00,
                            currencyCode: "USD"
                        },
                        readyExpectationLabel: "Ready in 1 hour",
                        proximityLabel: "Near Elm St",
                    }
                ];
            } else if (buyerLocation.countryCode === "CA") {
                pickupLocations = [
                    {
                        label: "Location A (Canada)",
                        code: "PICKUP_CA",
                        detail: "123 Main St, City, Canada",
                        amount: {
                            amount: 8.00,
                            currencyCode: "USD"
                        },
                        readyExpectationLabel: "Ready in 1 hour",
                        proximityLabel: "Near Main St",
                    },
                    {
                        label: "Location B (Canada)",
                        code: "PICKUP_CA",
                        detail: "456 Elm St, City, Canada",
                        amount: {
                            amount: 8.00,
                            currencyCode: "USD"
                        },
                        readyExpectationLabel: "Ready in 1 hour",
                        proximityLabel: "Near Elm St",
                    }
                ];
            }

            const updatedPaymentRequest = window.ShopPay.PaymentRequest.build({
                ...currentPaymentRequest,
                pickupLocations,
                supportedDeliveryMethodTypes: ["SHIPPING", "PICKUP"]
            });
            session.completePickupLocationFilterChange({ updatedPaymentRequest: updatedPaymentRequest });
        });

        session.addEventListener("discountcodechanged", async (ev) => {
            const currentPaymentRequest = session.paymentRequest;
            const selectedDiscountCodes = ev.discountCodes.map(code => code.toLowerCase());

            // Initialize discounts and updated line items
            let discounts = [];
            let updatedLineItems = [...currentPaymentRequest.lineItems];
            let totalDiscountAmount = 0;
            let errors = [];

            // Apply discounts based on the discount codes
            selectedDiscountCodes.forEach(code => {
                if (code === "lineitem") {
                    // Apply 15% discount on the line items
                    updatedLineItems = updatedLineItems.map(item => {
                        const discountAmount = item.originalItemPrice.amount * 0.15;
                        totalDiscountAmount += discountAmount * item.quantity;
                        return {
                            ...item,
                            finalItemPrice: {
                                amount: item.originalItemPrice.amount - discountAmount,
                                currencyCode: item.originalItemPrice.currencyCode
                            },
                            itemDiscounts: [
                                {
                                    label: "15% off",
                                    amount: {
                                        amount: discountAmount,
                                        currencyCode: item.originalItemPrice.currencyCode
                                    }
                                }
                            ]
                        };
                    });
                    discounts.push({
                        label: "15% off",
                        amount: {
                            amount: totalDiscountAmount,
                            currencyCode: "USD"
                        }
                    });
                } else if (code === "shipping" && currentDeliveryMethodType === "SHIPPING") {
                    // Make shipping free
                    const shippingDiscountAmount = originalRates[currentPaymentRequest.shippingLines[0].code];
                    totalDiscountAmount += shippingDiscountAmount;
                    discounts.push({
                        label: "Free Shipping",
                        amount: {
                            amount: shippingDiscountAmount,
                            currencyCode: "USD"
                        }
                    });
                } else if (code === "pickup" && currentDeliveryMethodType === "PICKUP") {
                    // Make pickup free
                    const pickupDiscountAmount = originalRates.PICKUP_US; // Assuming pickup is free
                    totalDiscountAmount += pickupDiscountAmount;
                    discounts.push({
                        label: "Free Pickup",
                        amount: {
                            amount: pickupDiscountAmount,
                            currencyCode: "USD"
                        }
                    });
                } else if (code === "line") {
                    // Apply $5 discount against the order
                    totalDiscountAmount += 5;
                    discounts.push({
                        label: "$5 off",
                        amount: {
                            amount: 5,
                            currencyCode: "USD"
                        }
                    });
                } else {
                    // Invalid discount code or not applicable for current delivery method type
                    errors.push({
                        type: "generalError",
                        message: `Invalid or inapplicable discount code: ${code}`
                    });
                    // Remove the invalid or inapplicable discount code
                    const index = selectedDiscountCodes.indexOf(code);
                    if (index > -1) {
                        selectedDiscountCodes.splice(index, 1);
                    }
                }
            });

            // Reset the shipping/pickup amount to its original value
            let deliveryAmount = currentPaymentRequest.totalShippingPrice.finalTotal.amount;
            if (!selectedDiscountCodes.includes("shipping") && !selectedDiscountCodes.includes("pickup")) {
                const selectedDeliveryMethod = currentPaymentRequest.shippingLines[0];
                if (selectedDeliveryMethod) {
                    deliveryAmount = originalRates[selectedDeliveryMethod.code] || deliveryAmount;
                }
            } else {
                if (selectedDiscountCodes.includes("shipping")) {
                    deliveryAmount = 0; // Apply free shipping if the discount code is "shipping"
                } else if (selectedDiscountCodes.includes("pickup")) {
                    deliveryAmount = 0; // Apply free pickup if the discount code is "pickup"
                }
            }

            // Calculate the new subtotal and total
            const newSubtotal = updatedLineItems.reduce((acc, item) => acc + item.finalLinePrice.amount, 0);
            const newTotal = newSubtotal + currentPaymentRequest.totalTax.amount + deliveryAmount - totalDiscountAmount;

            // Ensure totalShippingPrice is correctly updated
            const totalShippingPrice = {
                finalTotal: {
                    amount: deliveryAmount,
                    currencyCode: "USD"
                }
            };

            // Log the updated values for debugging
            console.log("Updated Line Items:", updatedLineItems);
            console.log("Total Discount Amount:", totalDiscountAmount);
            console.log("New Subtotal:", newSubtotal);
            console.log("New Total:", newTotal);
            console.log("Total Shipping Price:", totalShippingPrice);

            const updatedPaymentRequest = window.ShopPay.PaymentRequest.build({
                ...currentPaymentRequest,
                lineItems: updatedLineItems,
                subtotal: {
                    amount: newSubtotal,
                    currencyCode: "USD"
                },
                discounts: discounts,
                discountCodes: selectedDiscountCodes,
                total: {
                    amount: newTotal,
                    currencyCode: "USD"
                },
                supportedDeliveryMethodTypes: ["SHIPPING", "PICKUP"],
                totalShippingPrice: totalShippingPrice
            });

            session.completeDiscountCodeChange({ updatedPaymentRequest: updatedPaymentRequest, errors: errors });
        });

        session.addEventListener("paymentconfirmationrequested", async (ev) => {
            const currentPaymentRequest = session.paymentRequest;
            const { shippingAddress, supportedDeliveryMethodTypes, pickupLocations, ...rest } = currentPaymentRequest; // Destructure to remove shippingAddress

            if (rest.total.amount > 1000) {
                session.completePaymentConfirmationRequest({
                    errors: [
                        {
                            "type": "generalError",
                            "message": "Your order is over $1000. Please remove some items from your order."
                        }
                    ]
                });
                return;
            }

            // Ensure totalShippingPrice is a key-value object
            let totalShippingPrice = rest.totalShippingPrice;
            if (totalShippingPrice && Array.isArray(totalShippingPrice)) {
                totalShippingPrice = totalShippingPrice[0];
            }

            const updatedPaymentRequest = window.ShopPay.PaymentRequest.build({
                ...rest,
                totalShippingPrice: {
                    finalTotal: {
                        amount: totalShippingPrice ? totalShippingPrice.finalTotal.amount : 0,
                        currencyCode: totalShippingPrice ? totalShippingPrice.finalTotal.currencyCode : "USD"
                    }
                },
            });

            const response = await fetch('/server.php', {
                method: 'POST',
                body: JSON.stringify({
                    action: 'submitSession',
                    token: session.token,
                    payment_request: updatedPaymentRequest // Use the updated payment request
                }),
                headers: {
                    'Content-Type': 'application/json',
                },
            });

            const data = await response.json();
            if (data.errors) {
                session.completePaymentConfirmationRequest({
                    errors: [
                        {
                            "type": "generalError",
                            "message": "Something went wrong. Please try again."
                        }
                    ]
                });
            } else {
                session.completePaymentConfirmationRequest();
            }
        });

        session.addEventListener("paymentcomplete", async (ev) => {
            if (ev.processingStatus.status === "completed") {
                // Log the success event to the console
                console.log("Payment completed successfully.");
                console.log(`Card Type:`, ev.processingStatus);

                // Close the session and redirect to thank-you page
                session.close();
                // window.location.href = "/thank-you";
            } else {
                // handle failure
            }
        });

        session.addEventListener("windowclosed", async () => {
            // handle window closed event
        });
    </script>
</body>
</html>