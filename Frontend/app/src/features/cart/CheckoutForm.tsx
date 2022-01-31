import React from "react";
import { useStripe, useElements, CardElement } from "@stripe/react-stripe-js";
import { toast } from "react-toastify";

import Button from "../../common/Button";

import { useAppSelector, useAppDispatch } from "../../app/hooks";
import { selectCanCheckout, submitCheckout } from "./cartSlice";

const useOptions = () => {
  return {
    style: {
      base: {
        fontSize: "18px",
        fontFamily: "'Open Sans', sans-serif",
        fontSmoothing: "antialiased",
        lineHeight: "38px",
        borderRadius: "10px",
        iconColor: "#6d6d6d",
        color: "#424770",
        letterSpacing: "0.025em",
        backgroundColor: "#ffffff",
        "::placeholder": {
          color: "#424770",
        },
      },
      invalid: {
        color: "#9e2146",
      },
    },
  };
};

const CheckoutForm = () => {
  const dispatch = useAppDispatch();
  const stripe = useStripe();
  const elements = useElements();
  const options = useOptions();

  const canCheckout = useAppSelector(selectCanCheckout);

  const handleSubmit = async (event: any) => {
    event.preventDefault();

    if (!stripe || !elements) {
      return;
    }

    const card = elements.getElement(CardElement);
    if (!card) {
      return;
    }

    const result = await stripe.createToken(card);

    if (result.error) {
      toast.error(`Error! ${result.error}`);
      console.log(result.error.message);
    } else {
      dispatch(submitCheckout(result.token));
      card.clear();
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <CardElement options={options} />
      <Button
        disabled={!stripe || !canCheckout}
        style={{ marginTop: "2rem", float: "right" }}
        light
      >
        Submit
      </Button>
    </form>
  );
};

export default CheckoutForm;
