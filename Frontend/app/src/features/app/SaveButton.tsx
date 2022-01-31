import React, { SyntheticEvent } from "react";
import styled from "styled-components";

import Button from "../../common/Button";

import { useAppSelector, useAppDispatch } from "../../app/hooks";

import { selectCanSave } from "./appSlice";
import { updateReservations } from "../reservations/reservationsSlice";
import { updateLjas } from "../ljas/ljasSlice";

const Wrapper = styled.div`
  min-height: 50px;
`;

const SaveButton = () => {
  const dispatch = useAppDispatch();

  const canSave = useAppSelector(selectCanSave);

  const handleClick = (e: SyntheticEvent) => {
    e.preventDefault();

    dispatch(updateLjas());
    dispatch(updateReservations());
  };

  return (
    <Wrapper>
      {canSave && (
        <Button light fullWidth onClick={handleClick}>
          Save Changes
        </Button>
      )}
    </Wrapper>
  );
};

export default SaveButton;
