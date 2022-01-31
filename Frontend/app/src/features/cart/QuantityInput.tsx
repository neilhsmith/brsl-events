import React, { ChangeEvent } from "react";
import styled from "styled-components";
import { ILja } from "../ljas/ljas.types";

import { useAppDispatch, useAppSelector } from "../../app/hooks";
import { selectCartCountByLjaId, updateCartCount } from "./cartSlice";
import { selectRoleEnabled, selectCanRelinquishByRole } from "../app/appSlice";

const Wrapper = styled.div`
  margin-bottom: 1rem;
`;

const Label = styled.label`
  min-width: 180px;
`;

const Input = styled.input`
  display: inline-block !important;
  width: 72px !important;
`;

interface QuantityInputProps {
  lja: ILja;
  onChange: (ljaId: number, count: number) => void;
}

const QuantityInput = ({ lja, onChange }: QuantityInputProps) => {
  const dispatch = useAppDispatch();
  const count = useAppSelector(selectCartCountByLjaId)(lja.id);
  const roleEnabled = useAppSelector(selectRoleEnabled)(lja.role);
  const canRelinquish = useAppSelector(selectCanRelinquishByRole)(lja.role);

  let shouldRender = roleEnabled;
  if (canRelinquish) {
    if (!lja.acknowledgesRelinquish && !lja.didRelinquish) {
      shouldRender = false;
    }
  }

  const handleChange = (event: ChangeEvent<HTMLInputElement>) => {
    event.preventDefault();

    const val = event.currentTarget.value;
    onChange(lja.id, parseInt(val));
  };

  return shouldRender ? (
    <Wrapper>
      <Label>
        {lja.firstName} {lja.lastName}
      </Label>
      <Input type="number" min="0" value={count} onChange={handleChange} />
    </Wrapper>
  ) : null;
};

export default QuantityInput;
