-- Table: public."user"

-- DROP TABLE public."user";

CREATE TABLE public."user"
(
  id serial NOT NULL,
  created timestamp without time zone NOT NULL DEFAULT now(),
  active boolean NOT NULL DEFAULT true,
  userlevel integer NOT NULL DEFAULT 0,
  username character varying(16) NOT NULL,
  password character varying(50) NOT NULL,
  email character varying(100) NOT NULL,
  can_approve_orders boolean NOT NULL DEFAULT false,
  CONSTRAINT user_pkey PRIMARY KEY (id)
)
WITH (
OIDS=FALSE
);
ALTER TABLE public."user"
OWNER TO "o2-carpool";
