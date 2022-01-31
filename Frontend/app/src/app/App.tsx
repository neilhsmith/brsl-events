import React from "react";
import { Provider } from "react-redux";
import styled from "styled-components";

import store from "./store";

import { ToastContainer } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";

// @ts-expect-error
import View from "react-flexbox";

import Wrapper from "./Wrapper";
import PurchaseForm from "../features/cart/PurchaseForm";
import LjaAccordion from "../features/ljas/LjaAccordion";
import SaveButton from "../features/app/SaveButton";

const Row = styled.div`
  display: flex;
  flex-direction: row;
  align-items: center;
  margin-bottom: 1rem;
`;
const RowItem = styled.div`
  width: 50%;
`;

const App = () => {
  return (
    <Provider store={store}>
      <Wrapper>
        <div className="app">
          <ToastContainer closeButton={false} />
          <Row>
            <RowItem>
              <h1>Assign &amp; Purchase Seats</h1>
            </RowItem>
            <RowItem>
              <SaveButton />
            </RowItem>
          </Row>
          <LjaAccordion />
          <PurchaseForm />
        </div>
      </Wrapper>
    </Provider>
  );
};

export default App;
