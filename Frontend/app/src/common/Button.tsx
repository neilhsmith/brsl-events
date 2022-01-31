import styled from "styled-components";

interface ButtonProps {
  fullWidth?: boolean;
  light?: boolean;
}

export default styled.button`
  border: 2px solid #000000 !important;

  width: ${(props: ButtonProps) =>
    props.fullWidth ? "100%" : "auto"} !important;
  background-color: ${(props: ButtonProps) =>
    props.light ? "#ffffff" : "#000000"} !important;
  color: ${(props: ButtonProps) =>
    props.light ? "#000000" : "#ffffff"} !important;

  &:hover {
    color: ${(props: ButtonProps) =>
      props.light ? "#ffffff" : "#000000"} !important;
    background-color: ${(props: ButtonProps) =>
      props.light ? "#000000" : "#ffffff"} !important;
  }
`;
