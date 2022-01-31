import React from "react";
import styled from "styled-components";

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

const RelinquishedNotification = () => {
  return (
    <Wrapper>
      <Title>
        You have relinquished your 3 pre-paid seats and can no longer purchase
        additional seats.
      </Title>
      <Descrip>
        Please contact Kelli Pennington if you have any questions at{" "}
        <a href="tel:2259074905">225-907-4905</a> or{" "}
        <a href="mailto:brsl.ball.chair@gmail.com">brsl.ball.chair@gmail.com</a>
        .
      </Descrip>
    </Wrapper>
  );
};

export default RelinquishedNotification;
