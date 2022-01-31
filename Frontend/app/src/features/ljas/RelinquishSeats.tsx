import React, { SyntheticEvent } from "react";
import styled from "styled-components";
import moment from "moment";

import Button from "../../common/Button";

import { useAppSelector } from "../../app/hooks";
import {
  selectSeatCountByRole,
  selectRelinquishDateByRole,
} from "../app/appSlice";

import { ILja } from "./ljas.types";
import { useAppDispatch } from "../../app/hooks";
import { claimSeats, relinquishLjaSeats } from "./ljasSlice";

const Wrapper = styled.div`
  text-align: center;
  padding: 6rem;
`;
const Title = styled.h3`
  font-size: 3rem;
  margin-bottom: 2rem;
`;
const Descrip = styled.p`
  margin-bottom: 3rem;
`;
const SpacedButton = styled(Button)`
  margin: 0 1rem;
`;

interface RelinquishSeatsProps {
  lja: ILja;
}

const RelinquishSeats = ({ lja }: RelinquishSeatsProps) => {
  const dispatch = useAppDispatch();
  const prepaidCount = useAppSelector(selectSeatCountByRole)(lja.role);
  const relinquishDate = useAppSelector(selectRelinquishDateByRole)(lja.role);

  if (!relinquishDate) return null;

  const prettyDate = moment(relinquishDate.date).format("MMMM Do");

  const handleClaimSeats = () => {
    var confirmed = confirm(
      "Are you sure you want to use your pre-paid seats?"
    );

    if (confirmed) {
      dispatch(claimSeats(lja.id));
    }
  };

  const handleRelinquishSeats = (event: SyntheticEvent) => {
    var confirmed = confirm(
      `Are you sure you want to relinquish your ${prepaidCount} pre-paid seats seats? You will no longer be able to purchase additional seats.`
    );

    if (confirmed) {
      dispatch(relinquishLjaSeats(lja.id));
    }
  };

  return (
    <Wrapper>
      <Title>Are you planning on using your 3 pre-paid seats?</Title>
      <Descrip>
        If I <strong>DO NOT</strong> get a response from you by{" "}
        <strong>{prettyDate}</strong>, you forgo your {prepaidCount?.toString()}{" "}
        pre-paid seats.
      </Descrip>
      <SpacedButton onClick={handleClaimSeats}>
        I will use my seats
      </SpacedButton>
      <SpacedButton onClick={handleRelinquishSeats}>
        I will <strong>not</strong> use my seats
      </SpacedButton>
    </Wrapper>
  );
};

export default RelinquishSeats;
