CREATE TABLE customer
(
  id serial NOT NULL,
  created timestamp without time zone NOT NULL DEFAULT now(),
  email character varying(100) NOT NULL,
  phone character varying(15),
  is_confirmed boolean NOT NULL DEFAULT false,
  token_confirm character(32) DEFAULT NULL::bpchar,
  name_first character varying(35),
  name_last character varying(35),
  CONSTRAINT customer_pkey PRIMARY KEY (id)
)
WITH (
OIDS=FALSE
);
