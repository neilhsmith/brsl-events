import React from "react";
import styled from "styled-components";
import { Elements } from "@stripe/react-stripe-js";
import { loadStripe } from "@stripe/stripe-js";

import ClipLoader from "react-spinners/ClipLoader";

import QuantityInput from "./QuantityInput";
import CheckoutForm from "./CheckoutForm";

import { useAppDispatch, useAppSelector } from "../../app/hooks";
import { selectLjas } from "../ljas/ljasSlice";
import {
  getCartTotal,
  selectCartStatus,
  updateCartCount,
  selectCartItems,
} from "./cartSlice";
import { selectStripeKey, selectPurchasableCount } from "../app/appSlice";

declare global {
  interface Window {
    wpApiSettings: any;
  }
}
const stripePromise = loadStripe(window.wpApiSettings.stripePublishableKey);

const Wrapper = styled.div`
  position: relative;
  background-color: #f7f5f5;
  padding: 2rem 1rem;
  margin: 2rem 0;
`;

const Title = styled.h2`
  margin-bottom: 2rem;
  border-bottom: 1px solid #e0e0e0;
`;

const Total = styled.p`
  font-size: 1.9rem;
`;
const Price = styled.span`
  font-size: 2.8rem;
  font-weight: 700;
`;

const Flex = styled.div`
  display: flex;
  flex-direction: row;

  @media all and (max-width: 767px) {
    flex-direction: column;
  }
`;

const FlexItem = styled.div`
  width: 50%;
  padding: 0 1rem;

  @media all and (max-width: 767px) {
    width: 100%;
    margin-bottom: 3rem;
    padding: 0;
  }
`;

const LoadingBackground = styled.div`
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(247, 245, 245, 0.8);
  display: flex;
  justify-content: center;
  align-items: center;
`;

const PurchaseForm = () => {
  const dispatch = useAppDispatch();
  const ljas = useAppSelector(selectLjas);
  const total = useAppSelector(getCartTotal);
  const stripeSecret = useAppSelector(selectStripeKey);
  const cartItems = useAppSelector(selectCartItems);
  const purchasableCount = useAppSelector(selectPurchasableCount);
  const cartStatus = useAppSelector(selectCartStatus);

  const validLjas = ljas.filter((lja) => !lja.didRelinquish);

  // TODO: remove ljas from validLjas where:
  //         their class can relinquish but this lja has not relinquished or claimed

  const stripeOptions = {
    clientSecret: stripeSecret,
  };

  const updateQuantity = (ljaId: number, count: number) => {
    // TODO: don't dispatch the action & show notification if there's not enough seats left for sale

    const testCart = { ...cartItems };
    testCart[ljaId] = count;
    let total = 0;
    for (const prop in testCart) total += testCart[prop];

    if (purchasableCount && total <= purchasableCount) {
      dispatch(
        updateCartCount({
          ljaId,
          count,
        })
      );
    }
  };

  return (
    <Wrapper>
      <Flex>
        <FlexItem>
          <Title style={{ marginBottom: "4rem" }}>
            Purchase Additional Seats
          </Title>
          {validLjas &&
            validLjas.map((lja) => (
              <QuantityInput key={lja.id} lja={lja} onChange={updateQuantity} />
            ))}
        </FlexItem>
        <FlexItem>
          <Title>Checkout</Title>
          <Total>
            Total: <Price>${total}</Price>
          </Total>
          <Elements stripe={stripePromise} options={stripeOptions}>
            <CheckoutForm />
          </Elements>
        </FlexItem>
      </Flex>
      {cartStatus === "pending" && (
        <LoadingBackground>
          <ClipLoader size={100} />
        </LoadingBackground>
      )}
    </Wrapper>
  );
};

export default PurchaseForm;
