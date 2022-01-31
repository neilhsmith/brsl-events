import React, { SyntheticEvent } from "react";
import styled from "styled-components";
import { ILja } from "./ljas.types";

import Button from "../../common/Button";

import { useAppDispatch } from "../../app/hooks";
import { updateAcknowledgesResponsibility, updateLjas } from "./ljasSlice";

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

interface AcknowledgeResponsibilityProps {
  lja: ILja;
}
const AcknowledgeResponsibility = ({ lja }: AcknowledgeResponsibilityProps) => {
  const dispatch = useAppDispatch();

  const handleClick = (e: SyntheticEvent) => {
    e.preventDefault();
    dispatch(updateAcknowledgesResponsibility(lja.id));
  };

  return (
    <Wrapper>
      <Title>Acknowledge Responsibility</Title>
      <Descrip>
        I acknowledge, as the LJA Sponsor, I am responsible for my guests and
        the guests of my LJA the entire evening.
      </Descrip>
      <Button onClick={handleClick}>I Understand</Button>
    </Wrapper>
  );
};

export default AcknowledgeResponsibility;
