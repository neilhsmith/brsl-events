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

const DisabledRoleNotification = () => {
  return (
    <Wrapper>
      <Title>Check Back Later</Title>
      <Descrip>
        Seat purchasing and configuration is not available for this class yet.
      </Descrip>
    </Wrapper>
  );
};

export default DisabledRoleNotification;
